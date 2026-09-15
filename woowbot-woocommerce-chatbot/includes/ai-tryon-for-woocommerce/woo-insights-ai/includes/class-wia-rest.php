<?php
defined( 'ABSPATH' ) || exit;

final class QCLD_WIA_REST {
	public static function init() { add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) ); }

	public static function register_routes() {
		register_rest_route( 'woo-insights-ai/v1', '/ask', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'ask' ),
			'permission_callback' => function () { return current_user_can( 'manage_woocommerce' ) || current_user_can( 'manage_options' ); },
			'args'                => array( 'question' => array( 'required' => true, 'type' => 'string', 'minLength' => 2, 'maxLength' => 1000 ) ),
		) );
		register_rest_route( 'woo-insights-ai/v1', '/history', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'history' ),
				'permission_callback' => function () { return current_user_can( 'manage_woocommerce' ) || current_user_can( 'manage_options' ); },
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'clear_history' ),
				'permission_callback' => function () { return current_user_can( 'manage_woocommerce' ) || current_user_can( 'manage_options' ); },
			),
		) );
		register_rest_route( 'woo-insights-ai/v1', '/snapshot', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'snapshot' ),
			'permission_callback' => function () { return current_user_can( 'manage_woocommerce' ) || current_user_can( 'manage_options' ); },
		) );
	}

	public static function ask( WP_REST_Request $request ) {
		$history = self::get_history();
		$result = ( new QCLD_WIA_OpenAI() )->ask( $request->get_param( 'question' ), $history );
		if ( is_wp_error( $result ) ) { return $result; }
		$question = sanitize_textarea_field( $request->get_param( 'question' ) );
		$answer = sanitize_textarea_field( $result['answer'] ?? '' );
		$answer_html = self::format_answer_html( $answer );
		$history[] = array( 'role' => 'user', 'content' => $question );
		$history[] = array( 'role' => 'assistant', 'content' => $answer, 'visualizations' => $result['visualizations'] ?? array(), 'suggestions' => $result['suggestions'] ?? array() );
		update_user_meta( get_current_user_id(), 'qcld_wia_conversation_history', array_slice( $history, -20 ) );
		return rest_ensure_response( array( 'answer' => $answer, 'answer_html' => $answer_html, 'visualizations' => $result['visualizations'] ?? array(), 'suggestions' => $result['suggestions'] ?? array() ) );
	}

	public static function history() {
		$messages = self::get_history();
		foreach ( $messages as &$message ) { if ( 'assistant' === ( $message['role'] ?? '' ) ) { $message['answer_html'] = self::format_answer_html( $message['content'] ?? '' ); } }
		unset( $message );
		return rest_ensure_response( array( 'messages' => $messages ) );
	}

	public static function clear_history() {
		delete_user_meta( get_current_user_id(), 'qcld_wia_conversation_history' );
		return rest_ensure_response( array( 'cleared' => true ) );
	}

	public static function snapshot() {
		$result = ( new QCLD_WIA_Analytics() )->run( 'get_dashboard_snapshot', array() );
		return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
	}

	private static function get_history() {
		$history = get_user_meta( get_current_user_id(), 'qcld_wia_conversation_history', true );
		return is_array( $history ) ? array_slice( $history, -20 ) : array();
	}

	private static function format_answer_html( $markdown ) {
		$markdown = preg_replace_callback( '/\[\[order:(\d+)\|([^\]]+)\]\]/', function ( $match ) {
			$order = wc_get_order( (int) $match[1] );
			$url = $order && method_exists( $order, 'get_edit_order_url' ) ? $order->get_edit_order_url() : admin_url( 'post.php?post=' . (int) $match[1] . '&action=edit' );
			return '[' . $match[2] . '](' . $url . ')';
		}, (string) $markdown );
		$markdown = preg_replace_callback( '/\[\[customer:(\d+)\|([^\]]+)\]\]/', function ( $match ) { return '[' . $match[2] . '](' . admin_url( 'user-edit.php?user_id=' . (int) $match[1] ) . ')'; }, $markdown );
		$lines = preg_split( '/\R/', esc_html( $markdown ) ); $html = ''; $list = '';
		$close_list = function () use ( &$html, &$list ) { if ( $list ) { $html .= '</' . $list . '>'; $list = ''; } };
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( '' === $line ) { $close_list(); continue; }
			if ( preg_match( '/^[-*]\s+(.+)$/', $line, $match ) ) { if ( 'ul' !== $list ) { $close_list(); $html .= '<ul>'; $list = 'ul'; } $html .= '<li>' . self::inline_markdown( $match[1] ) . '</li>'; continue; }
			if ( preg_match( '/^\d+[.)]\s+(.+)$/', $line, $match ) ) { if ( 'ol' !== $list ) { $close_list(); $html .= '<ol>'; $list = 'ol'; } $html .= '<li>' . self::inline_markdown( $match[1] ) . '</li>'; continue; }
			$close_list();
			if ( preg_match( '/^(#{1,3})\s+(.+)$/', $line, $match ) ) { $level = min( 4, strlen( $match[1] ) + 1 ); $html .= '<h' . $level . '>' . self::inline_markdown( $match[2] ) . '</h' . $level . '>'; } else { $html .= '<p>' . self::inline_markdown( $line ) . '</p>'; }
		}
		$close_list();
		$allowed = array( 'p' => array(), 'strong' => array(), 'em' => array(), 'code' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(), 'a' => array( 'href' => true, 'target' => true, 'rel' => true ) );
		return wp_kses( $html, $allowed );
	}

	private static function inline_markdown( $text ) {
		$text = preg_replace( '/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text );
		$text = preg_replace( '/(?<!\*)\*([^*]+)\*(?!\*)/', '<em>$1</em>', $text );
		$text = preg_replace( '/`([^`]+)`/', '<code>$1</code>', $text );
		return preg_replace_callback( '/\[([^\]]+)\]\(([^\s)]+)\)/', function ( $match ) { $url = esc_url( html_entity_decode( $match[2], ENT_QUOTES, 'UTF-8' ) ); return $url ? '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $match[1] . '</a>' : $match[1]; }, $text );
	}
}

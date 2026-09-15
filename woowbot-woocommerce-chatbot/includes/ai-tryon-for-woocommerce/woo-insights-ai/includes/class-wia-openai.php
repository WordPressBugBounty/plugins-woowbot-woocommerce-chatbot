<?php
defined( 'ABSPATH' ) || exit;

final class QCLD_WIA_OpenAI {

	public function ask( $question, array $history = array() ) {
		$provider   = get_option( 'qcld_woo_chatbot_api_provider', 'gemini' );
		$openai_key = trim( (string) get_option( 'qcld_woo_chatbot_openai_api_key', '' ) );
		if ( empty( $openai_key ) ) {
			$openai_key = trim( (string) get_option( 'qcld_open_ai_api_key', '' ) );
		}
		if ( empty( $openai_key ) ) {
			$openai_key = trim( (string) get_option( 'qcld_wia_openai_api_key', '' ) );
		}

		$gemini_key = trim( (string) get_option( 'qcld_woo_chatbot_gemini_api_key', '' ) );
		if ( empty( $gemini_key ) ) {
			$gemini_key = trim( (string) get_option( 'qcld_gemini_api_key', '' ) );
		}

		if ( 'gemini' === $provider && ! empty( $gemini_key ) ) {
			return $this->ask_gemini( $question, $history, $gemini_key );
		} elseif ( 'openai' === $provider && ! empty( $openai_key ) ) {
			return $this->ask_openai( $question, $history, $openai_key );
		} elseif ( ! empty( $gemini_key ) ) {
			return $this->ask_gemini( $question, $history, $gemini_key );
		} elseif ( ! empty( $openai_key ) ) {
			return $this->ask_openai( $question, $history, $openai_key );
		}

		return new WP_Error( 'qcld_wia_no_api_key', __( 'Please configure your OpenAI or Google Gemini API key in AI Try-On & AI Settings.', 'woowbot-woocommerce-chatbot' ) );
	}

	private function ask_gemini( $question, array $history, $api_key ) {
		$saved_model = get_option( 'qcld_gemini_model', 'gemini-2.5-flash' );
		$model = ( ! empty( $saved_model ) && false === strpos( $saved_model, 'image' ) && false === strpos( $saved_model, 'veo' ) && false === strpos( $saved_model, 'video' ) ) ? $saved_model : 'gemini-2.5-flash';
		$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode( $model ) . ':generateContent?key=' . urlencode( $api_key );

		$contents = array();
		foreach ( array_slice( $history, -12 ) as $message ) {
			$role = ( 'assistant' === ( $message['role'] ?? '' ) ) ? 'model' : 'user';
			$content = sanitize_textarea_field( $message['content'] ?? '' );
			if ( '' !== $content ) {
				$contents[] = array(
					'role'  => $role,
					'parts' => array( array( 'text' => $content ) ),
				);
			}
		}
		$contents[] = array(
			'role'  => 'user',
			'parts' => array( array( 'text' => sanitize_textarea_field( $question ) ) ),
		);

		$body = array(
			'systemInstruction' => array(
				'parts' => array(
					array( 'text' => $this->instructions() . "\n\nYou MUST format your final response as a JSON object with two keys: \"answer\" (string in clean Markdown) and \"suggested_questions\" (array of exactly 4 specific follow-up questions)." ),
				),
			),
			'contents'          => $contents,
			'tools'             => $this->gemini_tools(),
		);

		$response = wp_remote_post( $url, array(
			'timeout' => 45,
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $body ),
		) );

		if ( is_wp_error( $response ) ) { return $response; }

		$status = wp_remote_retrieve_response_code( $response );
		$data   = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( $status < 200 || $status >= 300 ) {
			$message = $data['error']['message'] ?? __( 'Google Gemini request failed.', 'woowbot-woocommerce-chatbot' );
			return new WP_Error( 'qcld_wia_gemini_error', sanitize_text_field( $message ), array( 'status' => $status ) );
		}

		$candidate      = $data['candidates'][0] ?? array();
		$parts          = $candidate['content']['parts'] ?? array();
		$function_calls = array();
		foreach ( $parts as $part ) {
			if ( isset( $part['functionCall'] ) ) {
				$function_calls[] = $part['functionCall'];
			}
		}

		if ( ! empty( $function_calls ) ) {
			$visualizations      = array();
			$used_tools          = array();
			$tool_response_parts = array();

			foreach ( $function_calls as $call ) {
				$name         = $call['name'] ?? '';
				$used_tools[] = $name;
				$args         = $call['args'] ?? array();
				$result       = ( new QCLD_WIA_Analytics() )->run( $name, is_array( $args ) ? $args : array() );
				if ( is_wp_error( $result ) ) {
					$result = array( 'error' => $result->get_error_message() );
				}
				$visualization = $this->visualization( $name, $result );
				if ( $visualization ) {
					$visualizations[] = $visualization;
				}
				$tool_response_parts[] = array(
					'functionResponse' => array(
						'name'     => $name,
						'response' => array(
							'result' => $result,
						),
					),
				);
			}

			$contents[] = $candidate['content'];
			$contents[] = array(
				'role'  => 'user',
				'parts' => $tool_response_parts,
			);

			$second_body = array(
				'systemInstruction' => $body['systemInstruction'],
				'contents'          => $contents,
				'tools'             => $this->gemini_tools(),
			);

			$second_response = wp_remote_post( $url, array(
				'timeout' => 45,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( $second_body ),
			) );

			if ( is_wp_error( $second_response ) ) { return $second_response; }
			$second_data  = json_decode( wp_remote_retrieve_body( $second_response ), true );
			$second_parts = $second_data['candidates'][0]['content']['parts'] ?? array();
			$text         = '';
			foreach ( $second_parts as $part ) {
				if ( isset( $part['text'] ) ) {
					$text .= $part['text'];
				}
			}
			$payload = $this->answer_payload( $text, $used_tools );
			return array( 'answer' => $payload['answer'], 'visualizations' => $visualizations, 'suggestions' => $payload['suggestions'] );
		}

		$text = '';
		foreach ( $parts as $part ) {
			if ( isset( $part['text'] ) ) {
				$text .= $part['text'];
			}
		}
		$payload = $this->answer_payload( $text, array() );
		return array( 'answer' => $payload['answer'], 'visualizations' => array(), 'suggestions' => $payload['suggestions'] );
	}

	private function ask_openai( $question, array $history, $api_key ) {
		$saved_model = get_option( 'openai_engines', '' );
		if ( empty( $saved_model ) ) {
			$saved_model = get_option( 'qcld_wia_openai_model', 'gpt-4o-mini' );
		}
		$model = ! empty( $saved_model ) ? $saved_model : 'gpt-4o-mini';

		$messages = array(
			array( 'role' => 'system', 'content' => $this->instructions() . "\n\nYou MUST format your output as a JSON object with two keys: \"answer\" (markdown string) and \"suggested_questions\" (array of 4 distinct follow-up questions)." ),
		);
		foreach ( array_slice( $history, -12 ) as $message ) {
			$role    = ( 'assistant' === ( $message['role'] ?? '' ) ) ? 'assistant' : 'user';
			$content = sanitize_textarea_field( $message['content'] ?? '' );
			if ( '' !== $content ) {
				$messages[] = array( 'role' => $role, 'content' => function_exists( 'mb_substr' ) ? mb_substr( $content, 0, 2000 ) : substr( $content, 0, 2000 ) );
			}
		}
		$messages[] = array( 'role' => 'user', 'content' => sanitize_textarea_field( $question ) );

		$openai_tools = array();
		foreach ( $this->tools() as $tool ) {
			$openai_tools[] = array(
				'type'     => 'function',
				'function' => array(
					'name'        => $tool['name'],
					'description' => $tool['description'],
					'parameters'  => $tool['parameters'],
				),
			);
		}

		$body = array(
			'model'       => $model,
			'messages'    => $messages,
			'tools'       => $openai_tools,
			'tool_choice' => 'auto',
		);

		$response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', array(
			'timeout' => 45,
			'headers' => array( 'Authorization' => 'Bearer ' . $api_key, 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $body ),
		) );

		if ( is_wp_error( $response ) ) { return $response; }

		$status = wp_remote_retrieve_response_code( $response );
		$data   = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( $status < 200 || $status >= 300 ) {
			$message = $data['error']['message'] ?? __( 'OpenAI request failed.', 'woowbot-woocommerce-chatbot' );
			return new WP_Error( 'qcld_wia_openai_error', sanitize_text_field( $message ), array( 'status' => $status ) );
		}

		$choice     = $data['choices'][0]['message'] ?? array();
		$tool_calls = $choice['tool_calls'] ?? array();

		if ( ! empty( $tool_calls ) ) {
			$visualizations = array();
			$used_tools     = array();
			$messages[]     = $choice;

			foreach ( $tool_calls as $tool_call ) {
				$func_name    = $tool_call['function']['name'] ?? '';
				$used_tools[] = $func_name;
				$args         = json_decode( $tool_call['function']['arguments'] ?? '{}', true );
				$result       = ( new QCLD_WIA_Analytics() )->run( $func_name, is_array( $args ) ? $args : array() );
				if ( is_wp_error( $result ) ) {
					$result = array( 'error' => $result->get_error_message() );
				}
				$visualization = $this->visualization( $func_name, $result );
				if ( $visualization ) {
					$visualizations[] = $visualization;
				}
				$messages[] = array(
					'role'         => 'tool',
					'tool_call_id' => $tool_call['id'],
					'content'      => wp_json_encode( $result ),
				);
			}

			$second_body = array(
				'model'       => $model,
				'messages'    => $messages,
				'tool_choice' => 'none',
			);

			$second_response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', array(
				'timeout' => 45,
				'headers' => array( 'Authorization' => 'Bearer ' . $api_key, 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( $second_body ),
			) );

			if ( is_wp_error( $second_response ) ) { return $second_response; }
			$second_data = json_decode( wp_remote_retrieve_body( $second_response ), true );
			$text        = $second_data['choices'][0]['message']['content'] ?? '';
			$payload     = $this->answer_payload( $text, $used_tools );
			return array( 'answer' => $payload['answer'], 'visualizations' => $visualizations, 'suggestions' => $payload['suggestions'] );
		}

		$text    = $choice['content'] ?? '';
		$payload = $this->answer_payload( $text, array() );
		return array( 'answer' => $payload['answer'], 'visualizations' => array(), 'suggestions' => $payload['suggestions'] );
	}

	private function answer_payload( $text, array $tools ) {
		$text = trim( (string) $text );
		$json_str = $text;
		if ( preg_match( '/```(?:json)?\s*(\{[\s\S]*\})\s*```/i', $text, $matches ) ) {
			$json_str = $matches[1];
		}
		$data = json_decode( $json_str, true );
		if ( is_array( $data ) && ! empty( $data['answer'] ) ) {
			$suggestions = array();
			if ( ! empty( $data['suggested_questions'] ) ) {
				$suggestions = array_values( array_unique( array_filter( array_map( 'sanitize_text_field', (array) $data['suggested_questions'] ) ) ) );
			}
			if ( empty( $suggestions ) ) {
				$suggestions = $this->fallback_suggestions( $tools );
			}
			return array( 'answer' => sanitize_textarea_field( $data['answer'] ), 'suggestions' => array_slice( $suggestions, 0, 4 ) );
		}
		return array( 'answer' => $text, 'suggestions' => $this->fallback_suggestions( $tools ) );
	}

	private function fallback_suggestions( array $tools ) {
		if ( in_array( 'get_sales_trend', $tools, true ) ) {
			return array(
				__( 'Which period performed best?', 'woowbot-woocommerce-chatbot' ),
				__( 'Show the top products during the strongest period.', 'woowbot-woocommerce-chatbot' ),
				__( 'Compare this trend with the previous period.', 'woowbot-woocommerce-chatbot' ),
			);
		}
		if ( in_array( 'get_top_products', $tools, true ) ) {
			return array(
				__( 'Show the sales trend for the same period.', 'woowbot-woocommerce-chatbot' ),
				__( 'Compare these products with the previous period.', 'woowbot-woocommerce-chatbot' ),
				__( 'Summarize sales for this period.', 'woowbot-woocommerce-chatbot' ),
			);
		}
		if ( in_array( 'get_sales_summary', $tools, true ) ) {
			return array(
				__( 'Show this period as a sales graph.', 'woowbot-woocommerce-chatbot' ),
				__( 'Which products sold best during this period?', 'woowbot-woocommerce-chatbot' ),
				__( 'Compare this with the preceding period.', 'woowbot-woocommerce-chatbot' ),
			);
		}
		return array(
			__( 'What were total sales in the last 30 days?', 'woowbot-woocommerce-chatbot' ),
			__( 'Show the monthly sales trend for the last year.', 'woowbot-woocommerce-chatbot' ),
			__( 'What are my top 10 products this year?', 'woowbot-woocommerce-chatbot' ),
		);
	}

	private function visualization( $tool, $result ) {
		if ( ! is_array( $result ) ) { return null; }
		if ( 'get_sales_trend' === $tool && ! empty( $result['points'] ) ) {
			return array(
				'type'     => 'line',
				'title'    => __( 'Sales trend', 'woowbot-woocommerce-chatbot' ),
				'labels'   => array_column( $result['points'], 'period' ),
				'values'   => array_map( 'floatval', array_column( $result['points'], 'revenue' ) ),
				'currency' => sanitize_text_field( $result['currency'] ?? '' ),
			);
		}
		if ( 'get_top_products' === $tool && ! empty( $result['products'] ) ) {
			return array(
				'type'     => 'bar',
				'title'    => __( 'Top products by revenue', 'woowbot-woocommerce-chatbot' ),
				'labels'   => array_map( function ( $item ) { return sanitize_text_field( $item['product'] ?? '' ); }, $result['products'] ),
				'values'   => array_map( 'floatval', array_column( $result['products'], 'revenue' ) ),
				'currency' => sanitize_text_field( $result['currency'] ?? '' ),
			);
		}
		$map = array(
			'get_coupon_report'       => array( 'items' => 'coupons', 'label' => 'code', 'value' => 'revenue', 'title' => __( 'Coupon-attributed revenue', 'woowbot-woocommerce-chatbot' ) ),
			'get_customer_report'     => array( 'items' => 'customers', 'label' => 'name', 'value' => 'revenue', 'title' => __( 'Most valuable customers', 'woowbot-woocommerce-chatbot' ) ),
			'get_product_performance' => array( 'items' => 'products', 'label' => 'product', 'value' => ! empty( $result['cost_data_available'] ) ? 'profit' : 'revenue', 'title' => ! empty( $result['cost_data_available'] ) ? __( 'Product profit', 'woowbot-woocommerce-chatbot' ) : __( 'Product revenue', 'woowbot-woocommerce-chatbot' ) ),
			'get_sales_channels'      => array( 'items' => 'channels', 'label' => 'channel', 'value' => 'revenue', 'title' => __( 'Revenue by channel', 'woowbot-woocommerce-chatbot' ) ),
			'get_bought_together'     => array( 'items' => 'pairs', 'label' => 'product_a', 'value' => 'orders_together', 'title' => __( 'Frequently bought together', 'woowbot-woocommerce-chatbot' ) ),
		);
		if ( isset( $map[ $tool ] ) && ! empty( $result[ $map[ $tool ]['items'] ] ) ) {
			$config = $map[ $tool ]; $items = array_slice( $result[ $config['items'] ], 0, 12 );
			$labels = array_map( function ( $item ) use ( $tool, $config ) { return 'get_bought_together' === $tool ? ( $item['product_a'] . ' + ' . $item['product_b'] ) : $item[ $config['label'] ]; }, $items );
			return array( 'type' => 'bar', 'title' => $config['title'], 'labels' => $labels, 'values' => array_map( 'floatval', array_column( $items, $config['value'] ) ), 'currency' => 'get_bought_together' === $tool ? '' : ( $result['currency'] ?? '' ) );
		}
		return null;
	}

	private function instructions() {
		return 'You are a proactive WooCommerce business analyst in an ongoing conversation. Today is ' . current_time( 'Y-m-d' ) . ' in timezone ' . wp_timezone_string() . '. Resolve follow-up references from conversation history. Use analytics functions for every store-data claim and combine tools when needed to explain why sales changed or find growth opportunities. Analyze revenue drivers, products, margins, customers, repeat behavior, baskets, coupons, channels, and anomalies. Profit claims require cost_data_available=true; otherwise call the metric revenue. Customer reports may contain names and email addresses for the authorized store administrator. Tool results can contain internal references formatted exactly as [[customer:ID|label]] and [[order:ID|label]]; reproduce those references unchanged whenever mentioning that customer or order so the interface can create a verified local admin link. Never invent IDs, references, or figures. Make every answer easy to scan: lead with a short paragraph containing the main finding, use **bold** for the most important figures and conclusions, separate different ideas into short paragraphs, and use a concise bullet list for evidence or actions. Add a short Markdown heading when the answer has multiple sections. Avoid dense blocks of text. Give specific evidence, implications, and practical next actions. Every suggested question must be new, relevant to the latest evidence, different from recent questions, and designed to uncover an actionable insight that could increase sales.';
	}

	private function tools() {
		$date = array( 'type' => 'string', 'description' => 'Date in YYYY-MM-DD format.' );
		return array(
			$this->tool( 'get_recent_sales', 'Get the most recent paid or active store orders and their products.', array( 'limit' => array( 'type' => 'integer', 'minimum' => 1, 'maximum' => 20 ) ), array( 'limit' ) ),
			$this->tool( 'get_sales_summary', 'Get revenue, orders, items sold, and average order value for a date range.', array( 'start_date' => $date, 'end_date' => $date ), array( 'start_date', 'end_date' ) ),
			$this->tool( 'get_top_products', 'Get top products by net line revenue for a date range.', array( 'start_date' => $date, 'end_date' => $date, 'limit' => array( 'type' => 'integer', 'minimum' => 1, 'maximum' => 20 ) ), array( 'start_date', 'end_date', 'limit' ) ),
			$this->tool( 'get_sales_trend', 'Get revenue and order counts grouped over time.', array( 'start_date' => $date, 'end_date' => $date, 'interval' => array( 'type' => 'string', 'enum' => array( 'day', 'week', 'month' ) ) ), array( 'start_date', 'end_date', 'interval' ) ),
			$this->tool( 'compare_sales_periods', 'Compare revenue, orders, and average order value between two date ranges to explain sales changes.', array( 'first_start' => $date, 'first_end' => $date, 'second_start' => $date, 'second_end' => $date ), array( 'first_start', 'first_end', 'second_start', 'second_end' ) ),
			$this->tool( 'get_coupon_report', 'Audit coupon usage, attributed revenue, discount cost, order count, and average order value.', array( 'start_date' => $date, 'end_date' => $date, 'limit' => array( 'type' => 'integer', 'minimum' => 1, 'maximum' => 50 ) ), array( 'start_date', 'end_date', 'limit' ) ),
			$this->tool( 'get_customer_report', 'Find most valuable and likely repeat customers with customer profile references, names, emails, linked order references, order counts, revenue, AOV, and recency.', array( 'start_date' => $date, 'end_date' => $date, 'limit' => array( 'type' => 'integer', 'minimum' => 1, 'maximum' => 50 ) ), array( 'start_date', 'end_date', 'limit' ) ),
			$this->tool( 'get_product_performance', 'Rank products by revenue and, when WooCommerce cost data exists, profit and margin.', array( 'start_date' => $date, 'end_date' => $date, 'limit' => array( 'type' => 'integer', 'minimum' => 1, 'maximum' => 50 ) ), array( 'start_date', 'end_date', 'limit' ) ),
			$this->tool( 'get_bought_together', 'Find product pairs most frequently purchased in the same order.', array( 'start_date' => $date, 'end_date' => $date, 'limit' => array( 'type' => 'integer', 'minimum' => 1, 'maximum' => 50 ) ), array( 'start_date', 'end_date', 'limit' ) ),
			$this->tool( 'get_sales_channels', 'Report order revenue and counts by WooCommerce order attribution source or creation channel.', array( 'start_date' => $date, 'end_date' => $date ), array( 'start_date', 'end_date' ) ),
			$this->tool( 'get_store_anomalies', 'Detect unusual daily revenue spikes or drops within a date range.', array( 'start_date' => $date, 'end_date' => $date ), array( 'start_date', 'end_date' ) )
		);
	}

	private function gemini_tools() {
		$declarations = array();
		foreach ( $this->tools() as $tool ) {
			$props = array();
			foreach ( $tool['parameters']['properties'] as $prop_name => $prop_def ) {
				$type = strtoupper( $prop_def['type'] ?? 'STRING' );
				$p    = array(
					'type'        => $type,
					'description' => $prop_def['description'] ?? '',
				);
				if ( ! empty( $prop_def['enum'] ) ) {
					$p['enum'] = $prop_def['enum'];
				}
				$props[ $prop_name ] = $p;
			}
			$declarations[] = array(
				'name'        => $tool['name'],
				'description' => $tool['description'],
				'parameters'  => array(
					'type'       => 'OBJECT',
					'properties' => $props,
					'required'   => $tool['parameters']['required'] ?? array(),
				),
			);
		}
		return array(
			array(
				'functionDeclarations' => $declarations,
			),
		);
	}

	private function tool( $name, $description, $properties, $required ) {
		return array( 'type' => 'function', 'name' => $name, 'description' => $description, 'strict' => true, 'parameters' => array( 'type' => 'object', 'properties' => $properties, 'required' => $required, 'additionalProperties' => false ) );
	}
}

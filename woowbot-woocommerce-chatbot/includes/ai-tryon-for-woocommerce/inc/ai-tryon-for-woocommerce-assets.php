<?php
defined('ABSPATH') or die("No direct script access!");

add_action( 'admin_enqueue_scripts', 'qcld_woo_chatbot_admin_enqueue_assets' );
function qcld_woo_chatbot_admin_enqueue_assets( $hook ) {

    if ( strpos($hook, 'ai-tryon-for-woocommerce') !== false ) {
        
        wp_enqueue_style( 'qcld_woo_chatbot-admin-settings-css', ai_tryon_for_wc_asset_url. '/css/admin-settings.css', array(), ai_tryon_for_wc_version );
        wp_enqueue_script( 'qcld_woo_chatbot-admin-settings-js', ai_tryon_for_wc_asset_url. '/js/admin-settings.js', array('jquery'), ai_tryon_for_wc_version, true );
        wp_localize_script( 'qcld_woo_chatbot-admin-settings-js', 'qcld_woo_chatbot_admin_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'qcld_woo_chatbot_admin_nonce' ),
            'i18n'     => array(
                'reset'                 => esc_html__( 'Reset!', 'woowbot-woocommerce-chatbot' ),
                'enter_api_key'         => esc_html__( 'Please enter an API key first.', 'woowbot-woocommerce-chatbot' ),
                'testing_connection'    => esc_html__( 'Testing connection...', 'woowbot-woocommerce-chatbot' ),
                'error'                 => esc_html__( 'Error: ', 'woowbot-woocommerce-chatbot' ),
                'unknown_error'         => esc_html__( 'Unknown error', 'woowbot-woocommerce-chatbot' ),
                'request_failed'        => esc_html__( 'Request failed: ', 'woowbot-woocommerce-chatbot' ),
                'select_model'          => esc_html__( 'Please select a model first.', 'woowbot-woocommerce-chatbot' ),
                'verifying'             => esc_html__( 'Verifying...', 'woowbot-woocommerce-chatbot' ),
                'testing'               => esc_html__( 'Testing...', 'woowbot-woocommerce-chatbot' ),
                'verify'                => esc_html__( 'Verify', 'woowbot-woocommerce-chatbot' ),
                'verified'              => esc_html__( 'Verified! Model accepts this modality.', 'woowbot-woocommerce-chatbot' ),
                'ajax_failed'           => esc_html__( 'AJAX request failed.', 'woowbot-woocommerce-chatbot' ),
                'recommended_veo'       => esc_html__( 'Recommended (Optimal speed & pricing rate)', 'woowbot-woocommerce-chatbot' ),
            )
        ));
    }
}

// 3. Enqueue Styling Sheets and JS Actions
add_action( 'wp_enqueue_scripts', 'qcld_woo_chatbot_enqueue_assets' );
function qcld_woo_chatbot_enqueue_assets() {
    if ( is_product() ) {
        wp_enqueue_style( 'qcld_woo_chatbot-modal-css', ai_tryon_for_wc_asset_url. '/css/tryon-modal.css', array(), ai_tryon_for_wc_version );
        wp_enqueue_script( 'qcld_woo_chatbot-modal-js', ai_tryon_for_wc_asset_url. '/js/tryon-modal.js', array('jquery'), ai_tryon_for_wc_version, true );
        
        wp_localize_script( 'qcld_woo_chatbot-modal-js', 'qcld_woo_chatbot_ajax_obj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'qcld_woo_chatbot_generation_nonce' ),
            'i18n'     => array(
                'processing'        => esc_html__( 'Processing...', 'woowbot-woocommerce-chatbot' ),
                'generating_video'  => esc_html__( 'Generating Video (This may take up to 5 minutes)...', 'woowbot-woocommerce-chatbot' ),
                'status_error'      => esc_html__( 'Status Check Error: ', 'woowbot-woocommerce-chatbot' ),
                'execution_error'   => esc_html__( 'Execution Error: ', 'woowbot-woocommerce-chatbot' ),
                'unexpected_error'  => esc_html__( 'An unexpected infrastructure communication failure occurred.', 'woowbot-woocommerce-chatbot' ),
                'no_media'          => esc_html__( 'No media available to download.', 'woowbot-woocommerce-chatbot' ),
                'only_media'        => esc_html__( 'Only images/videos can be saved to the Media Library.', 'woowbot-woocommerce-chatbot' ),
                'saving'            => esc_html__( 'Saving...', 'woowbot-woocommerce-chatbot' ),
                'saved'             => esc_html__( 'Saved!', 'woowbot-woocommerce-chatbot' ),
                'error'             => esc_html__( 'Error', 'woowbot-woocommerce-chatbot' ),
                'error_saving'      => esc_html__( 'Error saving media.', 'woowbot-woocommerce-chatbot' ),
                'network_error'     => esc_html__( 'Network error.', 'woowbot-woocommerce-chatbot' ),
                'adding'            => esc_html__( 'Adding...', 'woowbot-woocommerce-chatbot' )
            )
        ));
    }
}


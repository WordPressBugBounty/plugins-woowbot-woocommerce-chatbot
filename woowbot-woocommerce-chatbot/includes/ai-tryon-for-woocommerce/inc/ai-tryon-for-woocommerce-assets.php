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
                'reset'                 => esc_html( 'Reset!', 'ai-tryon-for-woocommerce' ),
                'enter_api_key'         => esc_html( 'Please enter an API key first.', 'ai-tryon-for-woocommerce' ),
                'testing_connection'    => esc_html( 'Testing connection...', 'ai-tryon-for-woocommerce' ),
                'error'                 => esc_html( 'Error: ', 'ai-tryon-for-woocommerce' ),
                'unknown_error'         => esc_html( 'Unknown error', 'ai-tryon-for-woocommerce' ),
                'request_failed'        => esc_html( 'Request failed: ', 'ai-tryon-for-woocommerce' ),
                'select_model'          => esc_html( 'Please select a model first.', 'ai-tryon-for-woocommerce' ),
                'verifying'             => esc_html( 'Verifying...', 'ai-tryon-for-woocommerce' ),
                'testing'               => esc_html( 'Testing...', 'ai-tryon-for-woocommerce' ),
                'verify'                => esc_html( 'Verify', 'ai-tryon-for-woocommerce' ),
                'verified'              => esc_html( 'Verified! Model accepts this modality.', 'ai-tryon-for-woocommerce' ),
                'ajax_failed'           => esc_html( 'AJAX request failed.', 'ai-tryon-for-woocommerce' ),
                'recommended_veo'       => esc_html( 'Recommended (Optimal speed & pricing rate)', 'ai-tryon-for-woocommerce' ),
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
                'processing'        => esc_html( 'Processing...', 'ai-tryon-for-woocommerce' ),
                'generating_video'  => esc_html( 'Generating Video (This may take up to 5 minutes)...', 'ai-tryon-for-woocommerce' ),
                'status_error'      => esc_html( 'Status Check Error: ', 'ai-tryon-for-woocommerce' ),
                'execution_error'   => esc_html( 'Execution Error: ', 'ai-tryon-for-woocommerce' ),
                'unexpected_error'  => esc_html( 'An unexpected infrastructure communication failure occurred.', 'ai-tryon-for-woocommerce' ),
                'no_media'          => esc_html( 'No media available to download.', 'ai-tryon-for-woocommerce' ),
                'only_media'        => esc_html( 'Only images/videos can be saved to the Media Library.', 'ai-tryon-for-woocommerce' ),
                'saving'            => esc_html( 'Saving...', 'ai-tryon-for-woocommerce' ),
                'saved'             => esc_html( 'Saved!', 'ai-tryon-for-woocommerce' ),
                'error'             => esc_html( 'Error', 'ai-tryon-for-woocommerce' ),
                'error_saving'      => esc_html( 'Error saving media.', 'ai-tryon-for-woocommerce' ),
                'network_error'     => esc_html( 'Network error.', 'ai-tryon-for-woocommerce' ),
                'adding'            => esc_html( 'Adding...', 'ai-tryon-for-woocommerce' )
            )
        ));
    }
}


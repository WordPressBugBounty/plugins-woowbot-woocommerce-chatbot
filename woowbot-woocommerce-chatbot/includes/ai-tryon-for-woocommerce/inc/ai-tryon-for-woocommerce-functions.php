<?php
defined('ABSPATH') or die("No direct script access!");

// 2. Inject "Try On" Button into Single Product Page Layout
$qcld_woo_chatbot_hook = get_option('qcld_woo_chatbot_button_position', 'woocommerce_single_product_summary');
// Use a priority of 35 for the default summary hook to place it near the bottom. Other hooks can use standard priorities.
$qcld_woo_chatbot_priority = ($qcld_woo_chatbot_hook === 'woocommerce_single_product_summary') ? 35 : 15;
add_action( $qcld_woo_chatbot_hook, 'qcld_woo_chatbot_inject_tryon_button', $qcld_woo_chatbot_priority );
function qcld_woo_chatbot_inject_tryon_button() {
    global $product;
    if ( ! $product ) return;
    
    // Check if the Try-On button is enabled in settings
    $is_enabled = get_option('qcld_woo_chatbot_button_enabled', '1');
    if ( $is_enabled !== '1' ) {
        return;
    }
    
    // Check if it's restricted to logged-in users
    $require_login = get_option('qcld_woo_chatbot_logged_in_users_only', '0');
    $is_restricted = ($require_login === '1' && !is_user_logged_in());
    
    $btn_icon_key = get_option('qcld_woo_chatbot_button_icon', 'sparkles');
    $btn_text = get_option('qcld_woo_chatbot_button_text', 'Try with your photo');
    
    $icon_html = '';
    if ($btn_icon_key === 'sparkles') $icon_html = '✨';
    elseif ($btn_icon_key === 'magic_wand') $icon_html = '<svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 4px;"><path d="M15 4V2"/><path d="M15 16v-2"/><path d="M8 9h2"/><path d="M20 9h2"/><path d="M17.8 11.8 19 13"/><path d="M15 9h.01"/><path d="M17.8 6.2 19 5"/><path d="m3 21 9-9"/><path d="M12.2 6.2 11 5"/></svg>';
    elseif ($btn_icon_key === 'shirt') $icon_html = '👕';
    elseif ($btn_icon_key === 'âœ¨') $icon_html = '✨'; // backwards compatibility
    
    $full_btn_text = $btn_text;
    if ($icon_html !== '') {
        $full_btn_text = $icon_html . ' ' . $btn_text;
    }

    $image_id = $product->get_image_id();
    $image_url = $image_id ? wp_get_attachment_url( $image_id ) : '';
    
    echo '<div class="qcld_woo_chatbot-button-wrapper">';
    if ( $is_restricted ) {
        $login_url = esc_url( wp_login_url( get_permalink() ) );
        echo '<button type="button" class="button alt" onclick="alert(\'' . esc_js(esc_html('You must be logged in to use this feature.', 'ai-tryon-for-woocommerce')) . '\'); window.location.href=\'' . esc_url($login_url) . '\';">' . wp_kses_post($full_btn_text) . '</button>';
    } else {
        echo '<button type="button" id="qcld_woo_chatbot-open-modal-btn" class="button alt" data-product-title="' . esc_attr( $product->get_name() ) . '" data-product-image="' . esc_url( $image_url ) . '">' . wp_kses_post($full_btn_text) . '</button>';
    }
    echo '</div>';
    
    // Inline HTML for the modal layout structure (only if not restricted)
    if ( ! $is_restricted ) {
        add_action( 'wp_footer', 'qcld_woo_chatbot_render_modal_html' );
    }
}

function qcld_woo_chatbot_render_modal_html() {
    ?>
    <div id="qcld_woo_chatbot-tryon-modal" class="qcld_woo_chatbot-modal-overlay">
        <div class="qcld_woo_chatbot-modal-content">
            <div class="qcld_woo_chatbot-modal-header">
                <h2><?php esc_html_e( 'AI Product Generation', 'ai-tryon-for-woocommerce' ); ?></h2>
                <span class="qcld_woo_chatbot-close-modal">&times;</span>
            </div>
            
            <div class="qcld_woo_chatbot-modal-body">
                <!-- Left Column -->
                <div class="qcld_woo_chatbot-modal-col qcld_woo_chatbot-col-left">
                    <h3><?php esc_html_e( 'Original Image', 'ai-tryon-for-woocommerce' ); ?></h3>
                    <div class="qcld_woo_chatbot-original-images">
                        <img id="qcld_woo_chatbot-modal-product-image" src="" alt="<?php esc_attr_e( 'Original Product', 'ai-tryon-for-woocommerce' ); ?>" style="display: none;" />
                        <div class="qcld_woo_chatbot-image-placeholder" id="qcld_woo_chatbot-modal-second-image-placeholder">
                            <span class="dashicons dashicons-plus-alt2" style="font-size: 24px; width: 24px; height: 24px; margin-bottom: 5px;"></span>
                            <br><?php esc_html_e( 'Upload Image', 'ai-tryon-for-woocommerce' ); ?>
                        </div>
                        <input type="file" id="qcld_woo_chatbot-second-image-upload" accept="image/*" style="display:none;" />
                    </div>
                </div>

                <!-- Middle Column -->
                <div class="qcld_woo_chatbot-modal-col qcld_woo_chatbot-col-middle">
                    <div class="qcld_woo_chatbot-tabs">
                        <button class="qcld_woo_chatbot-tab active" data-type="image"><?php esc_html_e( 'Generate Image', 'ai-tryon-for-woocommerce' ); ?></button>
                    </div>

                    <!-- Image Form Fields -->
                    <div id="qcld_woo_chatbot-form-image" class="qcld_woo_chatbot-tab-content active">
                        <div class="qcld_woo_chatbot-form-group">
                            <label for="qcld_woo_chatbot-bg-preference"><?php esc_html_e( 'Background Preference', 'ai-tryon-for-woocommerce' ); ?></label>
                            <select id="qcld_woo_chatbot-bg-preference">
                                <option value="Studio"><?php esc_html_e( 'Studio', 'ai-tryon-for-woocommerce' ); ?></option>
                                <option value="Outdoor"><?php esc_html_e( 'Outdoor', 'ai-tryon-for-woocommerce' ); ?></option>
                                <option value="Lifestyle"><?php esc_html_e( 'Lifestyle', 'ai-tryon-for-woocommerce' ); ?></option>
                            </select>
                        </div>
                        <div class="qcld_woo_chatbot-form-group">
                            <label for="qcld_woo_chatbot-output-style"><?php esc_html_e( 'Output Style', 'ai-tryon-for-woocommerce' ); ?></label>
                            <select id="qcld_woo_chatbot-output-style">
                                <option value="Model Shoot"><?php esc_html_e( 'Model Shoot', 'ai-tryon-for-woocommerce' ); ?></option>
                                <option value="Flat Lay"><?php esc_html_e( 'Flat Lay', 'ai-tryon-for-woocommerce' ); ?></option>
                                <option value="Ghost Mannequin"><?php esc_html_e( 'Ghost Mannequin', 'ai-tryon-for-woocommerce' ); ?></option>
                            </select>
                        </div>

                    </div>

                    <div class="qcld_woo_chatbot-form-group">
                        <label for="qcld_woo_chatbot-prompt"><?php esc_html_e( 'Prompt (Optional)', 'ai-tryon-for-woocommerce' ); ?></label>
                        <textarea id="qcld_woo_chatbot-prompt" placeholder="<?php esc_attr_e( 'Add any specific instructions (optional)', 'ai-tryon-for-woocommerce' ); ?>"></textarea>
                    </div>

                    <div class="qcld_woo_chatbot-action-buttons">
                        <button id="qcld_woo_chatbot-submit-generation" class="qcld_woo_chatbot-btn qcld_woo_chatbot-btn-primary"><?php esc_html_e( 'Generate', 'ai-tryon-for-woocommerce' ); ?></button>
                        <button id="qcld_woo_chatbot-download-btn" class="qcld_woo_chatbot-btn qcld_woo_chatbot-btn-secondary" style="display:none;"><?php esc_html_e( 'Download', 'ai-tryon-for-woocommerce' ); ?></button>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="qcld_woo_chatbot-modal-col qcld_woo_chatbot-col-right">
                    <div class="qcld_woo_chatbot-output-header"><?php esc_html_e( 'Generated Output', 'ai-tryon-for-woocommerce' ); ?></div>
                    <div class="qcld_woo_chatbot-response-canvas" style="position:relative;">
                        <div id="qcld_woo_chatbot-result-placeholder"><?php esc_html_e( 'Ready to generate', 'ai-tryon-for-woocommerce' ); ?></div>
                        <div id="qcld_woo_chatbot-loading-spinner" class="qcld_woo_chatbot-hidden">
                            <div class="qcld_woo_chatbot-spinner-circle"></div>
                            <div class="qcld_woo_chatbot-spinner-text"><?php esc_html_e( 'Generating Magic...', 'ai-tryon-for-woocommerce' ); ?></div>
                        </div>
                        <div id="qcld_woo_chatbot-result-display"></div>
                    </div>
                    
                    <?php if ( get_option('qcld_woo_chatbot_modal_add_to_cart_btn_enabled', '1') === '1' ) : ?>
                    <div class="qcld_woo_chatbot-modal-footer-actions">
                        <button type="button" id="qcld_woo_chatbot-modal-add-to-cart-btn" class="qcld_woo_chatbot-btn qcld_woo_chatbot-btn-primary">
                            <span class="dashicons dashicons-cart"></span> <?php esc_html_e( 'Add to Cart', 'ai-tryon-for-woocommerce' ); ?>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

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
    if ( empty( $btn_text ) ) {
        $btn_text = esc_html__( 'Try with your photo', 'woowbot-woocommerce-chatbot' );
    }
    
    $icon_html = '';
    if ( $btn_icon_key === 'sparkles' || $btn_icon_key === '✨' || $btn_icon_key === 'âœ¨' ) {
        $icon_html = '<svg class="qcld_woo_chatbot-btn-icon-svg" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/><path d="M5 3v4"/><path d="M19 17v4"/><path d="M3 5h4"/><path d="M17 19h4"/></svg>';
    } elseif ( $btn_icon_key === 'magic_wand' ) {
        $icon_html = '<svg class="qcld_woo_chatbot-btn-icon-svg" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 4V2"/><path d="M15 16v-2"/><path d="M8 9h2"/><path d="M20 9h2"/><path d="M17.8 11.8 19 13"/><path d="M15 9h.01"/><path d="M17.8 6.2 19 5"/><path d="m3 21 9-9"/><path d="M12.2 6.2 11 5"/></svg>';
    } elseif ( $btn_icon_key === 'shirt' ) {
        $icon_html = '<svg class="qcld_woo_chatbot-btn-icon-svg" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.5a2 2 0 0 0 1.23 1.54L6 11.5V20a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-8.5l1.91-.77a2 2 0 0 0 1.23-1.54l.58-3.5a2 2 0 0 0-1.34-2.23z"/></svg>';
    } elseif ( $btn_icon_key !== 'none' ) {
        $icon_html = '<span class="qcld_woo_chatbot-btn-emoji">' . esc_html( $btn_icon_key ) . '</span>';
    }
    
    $btn_inner_html = '';
    if ( ! empty( $icon_html ) ) {
        $btn_inner_html .= '<span class="qcld_woo_chatbot-btn-icon">' . $icon_html . '</span>';
    }
    $btn_inner_html .= '<span class="qcld_woo_chatbot-btn-text">' . esc_html( $btn_text ) . '</span>';
    $btn_inner_html .= '<span class="qcld_woo_chatbot-btn-shine" aria-hidden="true"></span>';

    $image_id = $product->get_image_id();
    $image_url = $image_id ? wp_get_attachment_url( $image_id ) : '';
    
    echo '<div class="qcld_woo_chatbot-button-wrapper">';
    if ( $is_restricted ) {
        $login_url = esc_url( wp_login_url( get_permalink() ) );
        echo '<button type="button" class="qcld_woo_chatbot-tryon-btn button alt" onclick="alert(\'' . esc_js(esc_html__('You must be logged in to use this feature.', 'woowbot-woocommerce-chatbot')) . '\'); window.location.href=\'' . esc_url($login_url) . '\';">' . $btn_inner_html . '</button>';
    } else {
        echo '<button type="button" id="qcld_woo_chatbot-open-modal-btn" class="qcld_woo_chatbot-tryon-btn button alt" data-product-title="' . esc_attr( $product->get_name() ) . '" data-product-image="' . esc_url( $image_url ) . '">' . $btn_inner_html . '</button>';
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
                <div class="qcld_woo_chatbot-modal-title-wrap">
                    <span class="qcld_woo_chatbot-modal-title-badge"><?php esc_html_e( 'AI STUDIO', 'woowbot-woocommerce-chatbot' ); ?></span>
                    <h2><?php esc_html_e( 'Virtual Try-On & Studio', 'woowbot-woocommerce-chatbot' ); ?></h2>
                </div>
                <button type="button" class="qcld_woo_chatbot-close-modal" aria-label="<?php esc_attr_e( 'Close', 'woowbot-woocommerce-chatbot' ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            
            <div class="qcld_woo_chatbot-modal-body">
                <!-- Left Column: Source Images -->
                <div class="qcld_woo_chatbot-modal-col qcld_woo_chatbot-col-left">
                    <div class="qcld_woo_chatbot-section-title"><?php esc_html_e( 'Reference Images', 'woowbot-woocommerce-chatbot' ); ?></div>
                    <div class="qcld_woo_chatbot-original-images">
                        <div class="qcld_woo_chatbot-image-card">
                            <span class="qcld_woo_chatbot-card-tag"><?php esc_html_e( 'Product', 'woowbot-woocommerce-chatbot' ); ?></span>
                            <img id="qcld_woo_chatbot-modal-product-image" src="" alt="<?php esc_attr_e( 'Original Product', 'woowbot-woocommerce-chatbot' ); ?>" class="qcld_woo_chatbot-hidden" />
                        </div>
                        <div class="qcld_woo_chatbot-image-placeholder" id="qcld_woo_chatbot-modal-second-image-placeholder">
                            <div class="qcld_woo_chatbot-placeholder-inner">
                                <svg class="qcld_woo_chatbot-placeholder-svg" xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <span class="qcld_woo_chatbot-placeholder-title"><?php esc_html_e( 'Your Photo', 'woowbot-woocommerce-chatbot' ); ?></span>
                                <span class="qcld_woo_chatbot-placeholder-subtitle"><?php esc_html_e( 'Click to upload', 'woowbot-woocommerce-chatbot' ); ?></span>
                            </div>
                        </div>
                        <input type="file" id="qcld_woo_chatbot-second-image-upload" accept="image/*" class="qcld_woo_chatbot-hidden" />
                    </div>
                </div>

                <!-- Middle Column: Controls & Styling -->
                <div class="qcld_woo_chatbot-modal-col qcld_woo_chatbot-col-middle">
                    <div class="qcld_woo_chatbot-section-title"><?php esc_html_e( 'Generation Settings', 'woowbot-woocommerce-chatbot' ); ?></div>
                    <div class="qcld_woo_chatbot-tabs">
                        <button type="button" class="qcld_woo_chatbot-tab active" data-type="image">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                            <span><?php esc_html_e( 'Photo Try-On', 'woowbot-woocommerce-chatbot' ); ?></span>
                        </button>
                    </div>

                    <!-- Image Form Fields -->
                    <div id="qcld_woo_chatbot-form-image" class="qcld_woo_chatbot-tab-content active">
                        <div class="qcld_woo_chatbot-form-group">
                            <label for="qcld_woo_chatbot-bg-preference"><?php esc_html_e( 'Background Environment', 'woowbot-woocommerce-chatbot' ); ?></label>
                            <select id="qcld_woo_chatbot-bg-preference">
                                <option value="Studio"><?php esc_html_e( 'Clean Studio (Neutral)', 'woowbot-woocommerce-chatbot' ); ?></option>
                                <option value="Outdoor"><?php esc_html_e( 'Natural Outdoor / Street', 'woowbot-woocommerce-chatbot' ); ?></option>
                                <option value="Lifestyle"><?php esc_html_e( 'Modern Lifestyle / Interior', 'woowbot-woocommerce-chatbot' ); ?></option>
                            </select>
                        </div>
                        <div class="qcld_woo_chatbot-form-group">
                            <label for="qcld_woo_chatbot-output-style"><?php esc_html_e( 'Photography Style', 'woowbot-woocommerce-chatbot' ); ?></label>
                            <select id="qcld_woo_chatbot-output-style">
                                <option value="Model Shoot"><?php esc_html_e( 'Editorial Model Shoot', 'woowbot-woocommerce-chatbot' ); ?></option>
                                <option value="Flat Lay"><?php esc_html_e( 'Premium Flat Lay', 'woowbot-woocommerce-chatbot' ); ?></option>
                                <option value="Ghost Mannequin"><?php esc_html_e( 'Ghost Mannequin (3D Fit)', 'woowbot-woocommerce-chatbot' ); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="qcld_woo_chatbot-form-group">
                        <label for="qcld_woo_chatbot-prompt"><?php esc_html_e( 'Custom Instructions (Optional)', 'woowbot-woocommerce-chatbot' ); ?></label>
                        <textarea id="qcld_woo_chatbot-prompt" placeholder="<?php esc_attr_e( 'e.g. Natural daylight, smiling pose, casual fit...', 'woowbot-woocommerce-chatbot' ); ?>"></textarea>
                    </div>

                    <div class="qcld_woo_chatbot-action-buttons">
                        <button type="button" id="qcld_woo_chatbot-submit-generation" class="qcld_woo_chatbot-btn qcld_woo_chatbot-btn-primary">
                            <svg class="qcld_woo_chatbot-btn-icon-svg" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                            <span><?php esc_html_e( 'Generate Try-On', 'woowbot-woocommerce-chatbot' ); ?></span>
                        </button>
                        <button type="button" id="qcld_woo_chatbot-download-btn" class="qcld_woo_chatbot-btn qcld_woo_chatbot-btn-secondary qcld_woo_chatbot-hidden">
                            <svg class="qcld_woo_chatbot-btn-icon-svg" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            <span><?php esc_html_e( 'Download', 'woowbot-woocommerce-chatbot' ); ?></span>
                        </button>
                    </div>
                </div>

                <!-- Right Column: AI Canvas Output -->
                <div class="qcld_woo_chatbot-modal-col qcld_woo_chatbot-col-right">
                    <div class="qcld_woo_chatbot-output-header">
                        <span><?php esc_html_e( 'AI Render Output', 'woowbot-woocommerce-chatbot' ); ?></span>
                        <span class="qcld_woo_chatbot-live-status-dot" aria-hidden="true"></span>
                    </div>
                    <div class="qcld_woo_chatbot-response-canvas">
                        <div id="qcld_woo_chatbot-result-placeholder">
                            <div class="qcld_woo_chatbot-canvas-empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m10 15 5-3-5-3v6Z"/></svg>
                                <span><?php esc_html_e( 'Ready to generate', 'woowbot-woocommerce-chatbot' ); ?></span>
                                <small><?php esc_html_e( 'Click Generate Try-On to start', 'woowbot-woocommerce-chatbot' ); ?></small>
                            </div>
                        </div>
                        <div id="qcld_woo_chatbot-loading-spinner" class="qcld_woo_chatbot-hidden">
                            <div class="qcld_woo_chatbot-spinner-circle"></div>
                            <div class="qcld_woo_chatbot-spinner-text"><?php esc_html_e( 'Rendering AI Model...', 'woowbot-woocommerce-chatbot' ); ?></div>
                            <div class="qcld_woo_chatbot-spinner-sub"><?php esc_html_e( 'Synthesizing textures & lighting', 'woowbot-woocommerce-chatbot' ); ?></div>
                        </div>
                        <div id="qcld_woo_chatbot-result-display"></div>
                    </div>
                    
                    <?php if ( get_option('qcld_woo_chatbot_modal_add_to_cart_btn_enabled', '1') === '1' ) : ?>
                    <div class="qcld_woo_chatbot-modal-footer-actions">
                        <button type="button" id="qcld_woo_chatbot-modal-add-to-cart-btn" class="qcld_woo_chatbot-btn qcld_woo_chatbot-btn-primary">
                            <svg class="qcld_woo_chatbot-btn-icon-svg" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                            <span class="qcld_woo_chatbot-modal-btn-text"><?php esc_html_e( 'Add to Cart', 'woowbot-woocommerce-chatbot' ); ?></span>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

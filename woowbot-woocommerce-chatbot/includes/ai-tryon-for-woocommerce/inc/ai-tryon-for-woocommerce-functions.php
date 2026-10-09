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
    $is_variable = $product->is_type('variable');
    $is_sold_individually = $product->is_sold_individually();
    $min_qty = apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product );
    $max_qty = apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product );
    $step_qty = apply_filters( 'woocommerce_quantity_input_step', 1, $product );
    $attributes_json = '';
    $variations_json = '';
    if ( $is_variable ) {
        $attributes_json = wp_json_encode( $product->get_variation_attributes() );
        $variations_json = wp_json_encode( $product->get_available_variations() );
    }
    
    echo '<div class="qcld_woo_chatbot-button-wrapper">';
    if ( $is_restricted ) {
        $login_url = esc_url( wp_login_url( get_permalink() ) );
        $login_req_msg = get_option( 'qcld_woo_chatbot_txt_login_required', esc_html__( 'You must be logged in to use this feature.', 'woowbot-woocommerce-chatbot' ) );
        echo '<button type="button" class="qcld_woo_chatbot-tryon-btn button alt" onclick="alert(\'' . esc_js( $login_req_msg ) . '\'); window.location.href=\'' . esc_url($login_url) . '\';">' . $btn_inner_html . '</button>';
    } else {
        echo '<button type="button" id="qcld_woo_chatbot-open-modal-btn" class="qcld_woo_chatbot-tryon-btn button alt" data-product-id="' . esc_attr( $product->get_id() ) . '" data-product-title="' . esc_attr( $product->get_name() ) . '" data-product-image="' . esc_url( $image_url ) . '" data-is-variable="' . ($is_variable ? '1' : '0') . '" data-sold-individually="' . ($is_sold_individually ? '1' : '0') . '" data-min-qty="' . esc_attr( $min_qty ) . '" data-max-qty="' . esc_attr( $max_qty > 0 ? $max_qty : '' ) . '" data-step-qty="' . esc_attr( $step_qty ) . '"' . ($is_variable ? ' data-attributes="' . esc_attr($attributes_json) . '" data-variations="' . esc_attr($variations_json) . '"' : '') . '>' . $btn_inner_html . '</button>';
    }
    echo '</div>';
    
    // Inline HTML for the modal layout structure (only if not restricted)
    if ( ! $is_restricted ) {
        add_action( 'wp_footer', 'qcld_woo_chatbot_render_modal_html' );
    }
}

function qcld_woo_chatbot_render_modal_html() {
    $txt_modal_badge        = get_option( 'qcld_woo_chatbot_txt_modal_badge', esc_html__( 'AI STUDIO', 'woowbot-woocommerce-chatbot' ) );
    $txt_modal_title        = get_option( 'qcld_woo_chatbot_txt_modal_title', esc_html__( 'Virtual Try-On & Studio', 'woowbot-woocommerce-chatbot' ) );
    $txt_close              = get_option( 'qcld_woo_chatbot_txt_close', esc_attr__( 'Close', 'woowbot-woocommerce-chatbot' ) );
    $txt_ref_images_title   = get_option( 'qcld_woo_chatbot_txt_ref_images_title', esc_html__( 'Reference Images', 'woowbot-woocommerce-chatbot' ) );
    $txt_product_tag        = get_option( 'qcld_woo_chatbot_txt_product_tag', esc_html__( 'Product', 'woowbot-woocommerce-chatbot' ) );
    $txt_upload_title       = get_option( 'qcld_woo_chatbot_txt_upload_title', esc_html__( 'Your Photo', 'woowbot-woocommerce-chatbot' ) );
    $txt_upload_subtitle    = get_option( 'qcld_woo_chatbot_txt_upload_subtitle', esc_html__( 'Click to upload', 'woowbot-woocommerce-chatbot' ) );
    $txt_settings_title     = get_option( 'qcld_woo_chatbot_txt_settings_title', esc_html__( 'Generation Settings', 'woowbot-woocommerce-chatbot' ) );
    $txt_tab_photo_tryon    = get_option( 'qcld_woo_chatbot_txt_tab_photo_tryon', esc_html__( 'Photo Try-On', 'woowbot-woocommerce-chatbot' ) );
    $txt_bg_label           = get_option( 'qcld_woo_chatbot_txt_bg_label', esc_html__( 'Background Environment', 'woowbot-woocommerce-chatbot' ) );
    $txt_bg_studio          = get_option( 'qcld_woo_chatbot_txt_bg_studio', esc_html__( 'Clean Studio (Neutral)', 'woowbot-woocommerce-chatbot' ) );
    $txt_bg_outdoor         = get_option( 'qcld_woo_chatbot_txt_bg_outdoor', esc_html__( 'Natural Outdoor / Street', 'woowbot-woocommerce-chatbot' ) );
    $txt_bg_lifestyle       = get_option( 'qcld_woo_chatbot_txt_bg_lifestyle', esc_html__( 'Modern Lifestyle / Interior', 'woowbot-woocommerce-chatbot' ) );
    $txt_style_label        = get_option( 'qcld_woo_chatbot_txt_style_label', esc_html__( 'Photography Style', 'woowbot-woocommerce-chatbot' ) );
    $txt_style_model        = get_option( 'qcld_woo_chatbot_txt_style_model', esc_html__( 'Editorial Model Shoot', 'woowbot-woocommerce-chatbot' ) );
    $txt_style_flatlay      = get_option( 'qcld_woo_chatbot_txt_style_flatlay', esc_html__( 'Premium Flat Lay', 'woowbot-woocommerce-chatbot' ) );
    $txt_style_mannequin    = get_option( 'qcld_woo_chatbot_txt_style_mannequin', esc_html__( 'Ghost Mannequin (3D Fit)', 'woowbot-woocommerce-chatbot' ) );
    $txt_prompt_label       = get_option( 'qcld_woo_chatbot_txt_prompt_label', esc_html__( 'Custom Instructions (Optional)', 'woowbot-woocommerce-chatbot' ) );
    $txt_prompt_placeholder = get_option( 'qcld_woo_chatbot_txt_prompt_placeholder', esc_attr__( 'e.g. Natural daylight, smiling pose, casual fit...', 'woowbot-woocommerce-chatbot' ) );
    $txt_btn_generate       = get_option( 'qcld_woo_chatbot_txt_btn_generate', esc_html__( 'Generate Try-On', 'woowbot-woocommerce-chatbot' ) );
    $txt_btn_download       = get_option( 'qcld_woo_chatbot_txt_btn_download', esc_html__( 'Download', 'woowbot-woocommerce-chatbot' ) );
    $txt_output_title       = get_option( 'qcld_woo_chatbot_txt_output_title', esc_html__( 'AI Render Output', 'woowbot-woocommerce-chatbot' ) );
    $txt_ready_title        = get_option( 'qcld_woo_chatbot_txt_ready_title', esc_html__( 'Ready to generate', 'woowbot-woocommerce-chatbot' ) );
    $txt_ready_subtitle     = get_option( 'qcld_woo_chatbot_txt_ready_subtitle', esc_html__( 'Click Generate Try-On to start', 'woowbot-woocommerce-chatbot' ) );
    $txt_qty_minus          = get_option( 'qcld_woo_chatbot_txt_qty_minus', esc_attr__( 'Decrease quantity', 'woowbot-woocommerce-chatbot' ) );
    $txt_qty_plus           = get_option( 'qcld_woo_chatbot_txt_qty_plus', esc_attr__( 'Increase quantity', 'woowbot-woocommerce-chatbot' ) );
    $txt_btn_add_to_cart    = get_option( 'qcld_woo_chatbot_txt_btn_add_to_cart', esc_html__( 'Add to Cart', 'woowbot-woocommerce-chatbot' ) );
    ?>
    <div id="qcld_woo_chatbot-tryon-modal" class="qcld_woo_chatbot-modal-overlay">
        <div class="qcld_woo_chatbot-modal-content">
            <div class="qcld_woo_chatbot-modal-header">
                <div class="qcld_woo_chatbot-modal-title-wrap">
                    <span class="qcld_woo_chatbot-modal-title-badge"><?php echo esc_html( $txt_modal_badge ); ?></span>
                    <h2><?php echo esc_html( $txt_modal_title ); ?></h2>
                </div>
                <button type="button" class="qcld_woo_chatbot-close-modal" aria-label="<?php echo esc_attr( $txt_close ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            
            <div class="qcld_woo_chatbot-modal-body">
                <!-- Left Column: Source Images -->
                <div class="qcld_woo_chatbot-modal-col qcld_woo_chatbot-col-left">
                    <div class="qcld_woo_chatbot-section-title">
                        <span class="qcld_woo_chatbot-section-title-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                        </span>
                        <span><?php echo esc_html( $txt_ref_images_title ); ?></span>
                    </div>
                    <div class="qcld_woo_chatbot-original-images">
                        <div class="qcld_woo_chatbot-image-card">
                            <span class="qcld_woo_chatbot-card-tag"><?php echo esc_html( $txt_product_tag ); ?></span>
                            <img id="qcld_woo_chatbot-modal-product-image" src="" alt="<?php echo esc_attr( $txt_product_tag ); ?>" />
                            <div class="qcld_woo_chatbot-card-backdrop-overlay"></div>
                        </div>
                        <div class="qcld_woo_chatbot-image-placeholder" id="qcld_woo_chatbot-modal-second-image-placeholder">
                            <div class="qcld_woo_chatbot-placeholder-inner">
                                <div class="qcld_woo_chatbot-upload-icon-badge">
                                    <svg class="qcld_woo_chatbot-placeholder-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                </div>
                                <span class="qcld_woo_chatbot-placeholder-title"><?php echo esc_html( $txt_upload_title ); ?></span>
                                <span class="qcld_woo_chatbot-placeholder-subtitle"><?php echo esc_html( $txt_upload_subtitle ); ?></span>
                            </div>
                        </div>
                        <input type="file" id="qcld_woo_chatbot-second-image-upload" accept="image/*" class="qcld_woo_chatbot-hidden" />
                    </div>
                </div>

                <!-- Middle Column: Controls & Styling -->
                <div class="qcld_woo_chatbot-modal-col qcld_woo_chatbot-col-middle">
                    <div class="qcld_woo_chatbot-section-title">
                        <span class="qcld_woo_chatbot-section-title-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="21" y2="21"/><line x1="4" x2="20" y1="3" y2="3"/><line x1="4" x2="20" y1="12" y2="12"/><circle cx="8" cy="3" r="2"/><circle cx="16" cy="12" r="2"/><circle cx="10" cy="21" r="2"/></svg>
                        </span>
                        <span><?php echo esc_html( $txt_settings_title ); ?></span>
                    </div>
                    <div class="qcld_woo_chatbot-tabs">
                        <button type="button" class="qcld_woo_chatbot-tab active" data-type="image">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                            <span><?php echo esc_html( $txt_tab_photo_tryon ); ?></span>
                        </button>
                    </div>

                    <!-- Image Form Fields -->
                    <div id="qcld_woo_chatbot-form-image" class="qcld_woo_chatbot-tab-content active">
                        <div class="qcld_woo_chatbot-form-group">
                            <label for="qcld_woo_chatbot-bg-preference">
                                <span class="qcld_woo_chatbot-label-icon"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg></span>
                                <span><?php echo esc_html( $txt_bg_label ); ?></span>
                            </label>
                            <div class="qcld_woo_chatbot-select-wrap">
                                <select id="qcld_woo_chatbot-bg-preference">
                                    <option value="Studio"><?php echo esc_html( $txt_bg_studio ); ?></option>
                                    <option value="Outdoor"><?php echo esc_html( $txt_bg_outdoor ); ?></option>
                                    <option value="Lifestyle"><?php echo esc_html( $txt_bg_lifestyle ); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="qcld_woo_chatbot-form-group">
                            <label for="qcld_woo_chatbot-output-style">
                                <span class="qcld_woo_chatbot-label-icon"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.5a2 2 0 0 0 1.23 1.54L6 11.5V20a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-8.5l1.91-.77a2 2 0 0 0 1.23-1.54l.58-3.5a2 2 0 0 0-1.34-2.23z"/></svg></span>
                                <span><?php echo esc_html( $txt_style_label ); ?></span>
                            </label>
                            <div class="qcld_woo_chatbot-select-wrap">
                                <select id="qcld_woo_chatbot-output-style">
                                    <option value="Model Shoot"><?php echo esc_html( $txt_style_model ); ?></option>
                                    <option value="Flat Lay"><?php echo esc_html( $txt_style_flatlay ); ?></option>
                                    <option value="Ghost Mannequin"><?php echo esc_html( $txt_style_mannequin ); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="qcld_woo_chatbot-form-group">
                        <label for="qcld_woo_chatbot-prompt">
                            <span class="qcld_woo_chatbot-label-icon"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></span>
                            <span><?php echo esc_html( $txt_prompt_label ); ?></span>
                        </label>
                        <textarea id="qcld_woo_chatbot-prompt" placeholder="<?php echo esc_attr( $txt_prompt_placeholder ); ?>"></textarea>
                    </div>

                    <div class="qcld_woo_chatbot-action-buttons">
                        <button type="button" id="qcld_woo_chatbot-submit-generation" class="qcld_woo_chatbot-btn qcld_woo_chatbot-btn-primary">
                            <svg class="qcld_woo_chatbot-btn-icon-svg" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                            <span><?php echo esc_html( $txt_btn_generate ); ?></span>
                        </button>
                        <button type="button" id="qcld_woo_chatbot-download-btn" class="qcld_woo_chatbot-btn qcld_woo_chatbot-btn-secondary qcld_woo_chatbot-hidden">
                            <svg class="qcld_woo_chatbot-btn-icon-svg" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            <span><?php echo esc_html( $txt_btn_download ); ?></span>
                        </button>
                    </div>
                </div>

                <!-- Right Column: AI Canvas Output -->
                <div class="qcld_woo_chatbot-modal-col qcld_woo_chatbot-col-right">
                    <div class="qcld_woo_chatbot-output-header">
                        <div class="qcld_woo_chatbot-output-title-wrap">
                            <span class="qcld_woo_chatbot-section-title-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.64 3.64-1.28-1.28a1.21 1.21 0 0 0-1.72 0L2.36 18.64a1.21 1.21 0 0 0 0 1.72l1.28 1.28a1.2 1.2 0 0 0 1.72 0L21.64 5.36a1.2 1.2 0 0 0 0-1.72Z"/><path d="m14 7 3 3"/></svg>
                            </span>
                            <span><?php echo esc_html( $txt_output_title ); ?></span>
                        </div>
                        <div class="qcld_woo_chatbot-live-status-pill">
                            <span class="qcld_woo_chatbot-live-status-dot" aria-hidden="true"></span>
                            <span>Studio AI</span>
                        </div>
                    </div>
                    <div class="qcld_woo_chatbot-response-canvas">
                        <div id="qcld_woo_chatbot-result-placeholder">
                            <div class="qcld_woo_chatbot-canvas-empty-state">
                                <div class="qcld_woo_chatbot-canvas-empty-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/><path d="M5 3v4"/><path d="M19 17v4"/><path d="M3 5h4"/><path d="M17 19h4"/></svg>
                                </div>
                                <span class="qcld_woo_chatbot-empty-heading"><?php echo esc_html( $txt_ready_title ); ?></span>
                                <small class="qcld_woo_chatbot-empty-subheading"><?php echo esc_html( $txt_ready_subtitle ); ?></small>
                            </div>
                        </div>
                        <div id="qcld_woo_chatbot-loading-spinner" class="qcld_woo_chatbot-hidden">
                            <div class="qcld_woo_chatbot-spinner-circle"></div>
                            <div class="qcld_woo_chatbot-progress-wrap">
                                <div class="qcld_woo_chatbot-progress-bar">
                                    <div class="qcld_woo_chatbot-progress-fill" style="width: 0%;"></div>
                                </div>
                                <span class="qcld_woo_chatbot-progress-percent">0%</span>
                            </div>
                        </div>
                        <div id="qcld_woo_chatbot-result-display"></div>
                    </div>
                </div>
            </div>

            <?php if ( get_option('qcld_woo_chatbot_modal_add_to_cart_btn_enabled', '1') === '1' ) : ?>
            <div class="qcld_woo_chatbot-modal-footer">
                <div class="qcld_woo_chatbot-modal-footer-actions">
                    <div id="qcld_woo_chatbot-modal-variations-container" class="qcld_woo_chatbot-modal-variations-container qcld_woo_chatbot-hidden">
                        <!-- Variation selectors will be dynamically populated here for variable products -->
                    </div>
                    <div id="qcld_woo_chatbot-modal-quantity-wrapper" class="qcld_woo_chatbot-modal-quantity-wrapper">
                        <button type="button" class="qcld_woo_chatbot-qty-btn qcld_woo_chatbot-qty-minus" aria-label="<?php echo esc_attr( $txt_qty_minus ); ?>">&minus;</button>
                        <input type="number" id="qcld_woo_chatbot-modal-qty-input" class="qcld_woo_chatbot-modal-qty-input" value="1" min="1" step="1" inputmode="numeric" />
                        <button type="button" class="qcld_woo_chatbot-qty-btn qcld_woo_chatbot-qty-plus" aria-label="<?php echo esc_attr( $txt_qty_plus ); ?>">&plus;</button>
                    </div>
                    <button type="button" id="qcld_woo_chatbot-modal-add-to-cart-btn" class="qcld_woo_chatbot-btn qcld_woo_chatbot-btn-primary">
                        <svg class="qcld_woo_chatbot-btn-icon-svg" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                        <span><?php echo esc_html( $txt_btn_add_to_cart ); ?></span>
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

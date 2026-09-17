<?php
defined('ABSPATH') or die("No direct script access!");

// 1. Add Admin Settings Page for API Keys
add_action( 'admin_menu', 'qcld_woo_chatbot_add_admin_menu' );
function qcld_woo_chatbot_add_admin_menu() {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36"><path fill="#ffac33" d="M34.347 16.893l-8.899-3.294-3.323-10.891c-.128-.42-.517-.708-.956-.708-.439 0-.828.288-.956.708l-3.322 10.891-8.9 3.294c-.393.146-.653.519-.653.938 0 .418.26.793.653.938l8.895 3.293 3.324 11.223c.126.424.516.715.959.715.442 0 .833-.291.959-.716l3.324-11.223 8.896-3.293c.391-.144.652-.518.652-.937 0-.418-.261-.792-.653-.938z"/><path fill="#ffcc4d" d="M14.347 27.894l-2.314-.856-.9-3.3c-.118-.436-.513-.738-.964-.738-.451 0-.846.302-.965.737l-.9 3.3-2.313.856c-.393.145-.653.52-.653.938 0 .418.26.793.653.938l2.301.853.907 3.622c.112.444.511.756.97.756.459 0 .858-.312.97-.757l.907-3.622 2.301-.853c.393-.144.653-.519.653-.937 0-.418-.26-.793-.653-.937zM10.009 6.231l-2.364-.875-.876-2.365c-.145-.393-.519-.653-.938-.653-.418 0-.792.26-.938.653l-.875 2.365-2.365.875c-.393.146-.653.52-.653.938 0 .418.26.793.653.938l2.365.875.875 2.365c.146.393.52.653.938.653.418 0 .792-.26.938-.653l.875-2.365 2.365-.875c.393-.146.653-.52.653-.938 0-.418-.26-.792-.653-.938z"/></svg>';
    $icon = 'data:image/svg+xml;base64,' . base64_encode($svg);

    $menu_name = 'AI Studio';

    if (class_exists('QCLD_Woo_Chatbot')) {
        $menu_name = 'WoowBot - '. $menu_name;
    }


    add_menu_page( 'AI Studio Settings', $menu_name, 'manage_options', 'ai-tryon-for-woocommerce', 'qcld_woo_chatbot_ai_settings_page', $icon, 8 );
    
    // Submenus
    add_submenu_page( 
        'ai-tryon-for-woocommerce', 
        esc_html('AI Settings', 'woowbot-woocommerce-chatbot' ), 
        esc_html('AI Settings', 'woowbot-woocommerce-chatbot' ), 
        'manage_options', 
        'ai-tryon-for-woocommerce', 
        'qcld_woo_chatbot_ai_settings_page' 
    );
    add_submenu_page( 
        'ai-tryon-for-woocommerce', 
        esc_html('FitRoom TryOn', 'woowbot-woocommerce-chatbot' ), 
        esc_html('FitRoom TryOn', 'woowbot-woocommerce-chatbot' ), 
        'manage_options', 
        'ai-tryon-for-woocommerce-ai', 
        'qcld_woo_chatbot_settings_page' 
    );
}

function qcld_woo_chatbot_settings_page() {
    $selected_provider = get_option('qcld_woo_chatbot_api_provider', 'gemini');
    ?>
    <div class="wrap">
        <h2><?php esc_html_e( 'AI Try-On Configurations', 'woowbot-woocommerce-chatbot' ); ?></h2>
        <div class="qcld_woo_chatbot-admin-wrap">
            
            <form method="post" action="options.php" class="qcld_woo_chatbot-settings-form">
                <?php settings_fields( 'qcld_woo_chatbot_settings_group' ); ?>
                
                <div class="qcld_woo_chatbot-settings-grid">
                    
                    <div class="qcld_woo_chatbot-settings-card">
                        <div class="qcld_woo_chatbot-card-header">
                            <h2 class="qcld_woo_chatbot-admin-tabs">
                                <a href="#general" class="qcld_woo_chatbot-tab-link active" data-target="qcld_woo_chatbot-tab-general"><?php esc_html_e( 'General Settings', 'woowbot-woocommerce-chatbot' ); ?></a>
                                <a href="#prompts" class="qcld_woo_chatbot-tab-link" data-target="qcld_woo_chatbot-tab-prompts"><?php esc_html_e( 'Prompts', 'woowbot-woocommerce-chatbot' ); ?></a>
                                <a href="#help" class="qcld_woo_chatbot-tab-link" data-target="qcld_woo_chatbot-tab-help"><?php esc_html_e( 'Help', 'woowbot-woocommerce-chatbot' ); ?></a>
                            </h2>
                            <p class="qcld_woo_chatbot-mt-15"><?php esc_html_e( 'Configure all your AI Try-On settings in one place.', 'woowbot-woocommerce-chatbot' ); ?></p>
                        </div>
                        <div class="qcld_woo_chatbot-card-body">
                            
                            <div class="qcld_woo_chatbot-tab-content active" id="qcld_woo_chatbot-tab-general">
                                
                                <!-- General Settings Section -->
                                <div class="qcld_woo_chatbot-form-group">
                                    <label><?php esc_html_e( 'Enable Try-On Button', 'woowbot-woocommerce-chatbot' ); ?></label>
                                    <div class="qcld_woo_chatbot-toggle-wrapper">
                                        <label class="qcld_woo_chatbot-switch" for="qcld_woo_chatbot_button_enabled">
                                            <input type="hidden" name="qcld_woo_chatbot_button_enabled" value="0" />
                                            <input type="checkbox" id="qcld_woo_chatbot_button_enabled" name="qcld_woo_chatbot_button_enabled" value="1" <?php checked(get_option('qcld_woo_chatbot_button_enabled', '1'), '1'); ?> />
                                            <span class="qcld_woo_chatbot-slider"></span>
                                        </label>
                                        <span class="qcld_woo_chatbot-toggle-label"><?php esc_html_e( 'Show the "Try On with AI" button on single product pages', 'woowbot-woocommerce-chatbot' ); ?></span>
                                    </div>
                                </div>
                                
                                <div class="qcld_woo_chatbot-form-group">
                                    <label><?php esc_html_e( 'Require Login', 'woowbot-woocommerce-chatbot' ); ?></label>
                                    <div class="qcld_woo_chatbot-toggle-wrapper">
                                        <label class="qcld_woo_chatbot-switch" for="qcld_woo_chatbot_logged_in_users_only">
                                            <input type="hidden" name="qcld_woo_chatbot_logged_in_users_only" value="0" />
                                            <input type="checkbox" id="qcld_woo_chatbot_logged_in_users_only" name="qcld_woo_chatbot_logged_in_users_only" value="1" <?php checked(get_option('qcld_woo_chatbot_logged_in_users_only', '0'), '1'); ?> />
                                            <span class="qcld_woo_chatbot-slider"></span>
                                        </label>
                                        <span class="qcld_woo_chatbot-toggle-label"><?php esc_html_e( 'Show the Try-On feature for logged-in users only', 'woowbot-woocommerce-chatbot' ); ?></span>
                                    </div>
                                </div>
                                
                                <div class="qcld_woo_chatbot-form-group">
                                    <label><?php esc_html_e( 'Show Add to Cart Button', 'woowbot-woocommerce-chatbot' ); ?></label>
                                    <div class="qcld_woo_chatbot-toggle-wrapper">
                                        <label class="qcld_woo_chatbot-switch" for="qcld_woo_chatbot_modal_add_to_cart_btn_enabled">
                                            <input type="hidden" name="qcld_woo_chatbot_modal_add_to_cart_btn_enabled" value="0" />
                                            <input type="checkbox" id="qcld_woo_chatbot_modal_add_to_cart_btn_enabled" name="qcld_woo_chatbot_modal_add_to_cart_btn_enabled" value="1" <?php checked(get_option('qcld_woo_chatbot_modal_add_to_cart_btn_enabled', '1'), '1'); ?> />
                                            <span class="qcld_woo_chatbot-slider"></span>
                                        </label>
                                        <span class="qcld_woo_chatbot-toggle-label"><?php esc_html_e( 'Show the \'Add to Cart\' button in the Try-On modal footer', 'woowbot-woocommerce-chatbot' ); ?></span>
                                    </div>
                                </div>
                                
                                <div class="qcld_woo_chatbot-form-group">
                                    <label for="qcld_woo_chatbot_generation_limit"><?php esc_html_e( 'Daily Generation Limit', 'woowbot-woocommerce-chatbot' ); ?></label>
                                    <div class="qcld_woo_chatbot-input-action-group">
                                        <input type="number" id="qcld_woo_chatbot_generation_limit" name="qcld_woo_chatbot_generation_limit" class="qcld_woo_chatbot-input-field qcld_woo_chatbot-max-w-200" value="<?php echo esc_attr(get_option('qcld_woo_chatbot_generation_limit', '')); ?>" placeholder="<?php esc_attr_e( 'e.g. 5 (Leave blank for unlimited)', 'woowbot-woocommerce-chatbot' ); ?>" min="0" />
                                    </div>
                                    <span class="qcld_woo_chatbot-description"><?php esc_html_e( 'Limit Image and Video generation per session + IP per 24 hours. Leave blank or 0 for unlimited.', 'woowbot-woocommerce-chatbot' ); ?></span>
                                </div>

                                <div class="qcld_woo_chatbot-form-group qcld_woo_chatbot-full-width">
                                    <label><?php esc_html_e( 'Try-On Button Position', 'woowbot-woocommerce-chatbot' ); ?></label>
                                    <div class="qcld_woo_chatbot-radio-grid">
                                        <?php
                                        $selected_pos = get_option('qcld_woo_chatbot_button_position', 'woocommerce_single_product_summary');
                                        $positions = [
                                            'woocommerce_single_product_summary' => ['icon' => 'dashicons-layout', 'title' => esc_html__('Default', 'woowbot-woocommerce-chatbot'), 'desc' => esc_html__('Inside Product Summary', 'woowbot-woocommerce-chatbot')],
                                            'woocommerce_before_add_to_cart_form' => ['icon' => 'dashicons-arrow-up-alt2', 'title' => esc_html__('Before Cart', 'woowbot-woocommerce-chatbot'), 'desc' => esc_html__('Above Add to Cart Form', 'woowbot-woocommerce-chatbot')],
                                            'woocommerce_after_add_to_cart_form' => ['icon' => 'dashicons-arrow-down-alt2', 'title' => esc_html__('After Cart', 'woowbot-woocommerce-chatbot'), 'desc' => esc_html__('Below Add to Cart Form', 'woowbot-woocommerce-chatbot')],
                                            'woocommerce_product_meta_start' => ['icon' => 'dashicons-tag', 'title' => esc_html__('Before Meta', 'woowbot-woocommerce-chatbot'), 'desc' => esc_html__('Above Categories/Tags', 'woowbot-woocommerce-chatbot')],
                                            'woocommerce_product_meta_end' => ['icon' => 'dashicons-category', 'title' => esc_html__('After Meta', 'woowbot-woocommerce-chatbot'), 'desc' => esc_html__('Below Categories/Tags', 'woowbot-woocommerce-chatbot')],
                                            'woocommerce_after_single_product_summary' => ['icon' => 'dashicons-download', 'title' => esc_html__('Below Summary', 'woowbot-woocommerce-chatbot'), 'desc' => esc_html__('At bottom of product details', 'woowbot-woocommerce-chatbot')],
                                            'woocommerce_before_single_product_summary' => ['icon' => 'dashicons-upload', 'title' => esc_html__('Above Summary', 'woowbot-woocommerce-chatbot'), 'desc' => esc_html__('At very top of product details', 'woowbot-woocommerce-chatbot')]
                                        ];
                                        if(!empty($positions)){
                                            foreach ($positions as $hook => $data) {
                                                $active_class = ($selected_pos === $hook) ? 'active' : '';
                                                echo '<label class="qcld_woo_chatbot-radio-card ' . esc_attr($active_class) . '">';
                                                echo '<input type="radio" name="qcld_woo_chatbot_button_position" value="' . esc_attr($hook) . '" ' . checked($selected_pos, $hook, false) . ' />';
                                                echo '<span class="dashicons ' . esc_attr($data['icon']) . '"></span>';
                                                echo '<div class="qcld_woo_chatbot-radio-info">';
                                                echo '<strong>' . esc_html($data['title']) . '</strong>';
                                                echo '<span>' . esc_html($data['desc']) . '</span>';
                                                echo '</div>';
                                                echo '</label>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                                
                                <div class="qcld_woo_chatbot-form-group qcld_woo_chatbot-full-width">
                                    <label><?php esc_html_e( 'Try-On Button Icon', 'woowbot-woocommerce-chatbot' ); ?></label>
                                    <div class="qcld_woo_chatbot-radio-grid">
                                        <?php
                                        $saved_icon = get_option('qcld_woo_chatbot_button_icon', 'sparkles');
                                        $magic_wand_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="qcld_woo_chatbot-svg-align"><path d="M15 4V2"/><path d="M15 16v-2"/><path d="M8 9h2"/><path d="M20 9h2"/><path d="M17.8 11.8 19 13"/><path d="M15 9h.01"/><path d="M17.8 6.2 19 5"/><path d="m3 21 9-9"/><path d="M12.2 6.2 11 5"/></svg>';
                                        $icons = [
                                            'sparkles' => ['icon' => '✨', 'title' => esc_html__('Sparkles', 'woowbot-woocommerce-chatbot')],
                                            'magic_wand' => ['icon' => $magic_wand_svg, 'title' => esc_html__('Magic Wand', 'woowbot-woocommerce-chatbot')],
                                            'shirt' => ['icon' => '👕', 'title' => esc_html__('Shirt', 'woowbot-woocommerce-chatbot')],
                                            'none' => ['icon' => '🚫', 'title' => esc_html__('No Icon', 'woowbot-woocommerce-chatbot')]
                                        ];
                                        if(!empty($icons)){
                                            foreach ($icons as $val => $data) {
                                                $active_class = ($saved_icon === $val) ? 'active' : '';
                                                echo '<label class="qcld_woo_chatbot-radio-card ' . esc_attr($active_class) . '">';
                                                echo '<input type="radio" name="qcld_woo_chatbot_button_icon" value="' . esc_attr($val) . '" ' . checked($saved_icon, $val, false) . ' />';
                                                echo '<span class="qcld_woo_chatbot-icon-badge">' . wp_kses_post($data['icon']) . '</span>';
                                                echo '<div class="qcld_woo_chatbot-radio-info">';
                                                echo '<strong>' . esc_attr($data['title']) . '</strong>';
                                                echo '</div>';
                                                echo '</label>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <span class="qcld_woo_chatbot-description qcld_woo_chatbot-mt-10"><?php esc_html_e( 'Select an emoji icon to display before the button text.', 'woowbot-woocommerce-chatbot' ); ?></span>
                                </div>

                                <div class="qcld_woo_chatbot-form-group">
                                    <label for="qcld_woo_chatbot_button_text"><?php esc_html_e( 'Try with your photo Button Text', 'woowbot-woocommerce-chatbot' ); ?></label>
                                    <div class="qcld_woo_chatbot-input-action-group">
                                        <input type="text" id="qcld_woo_chatbot_button_text" name="qcld_woo_chatbot_button_text" class="qcld_woo_chatbot-input-field" value="<?php echo esc_attr(get_option('qcld_woo_chatbot_button_text', 'Try with your photo')); ?>" />
                                    </div>
                                    <span class="qcld_woo_chatbot-description"><?php esc_html_e( 'The text displayed on the frontend button.', 'woowbot-woocommerce-chatbot' ); ?></span>
                                </div>

                                

                                <div class="qcld_woo_chatbot-form-group qcld_woo_chatbot-full-width">
                                    <label><?php esc_html_e( 'Default Video Duration', 'woowbot-woocommerce-chatbot' ); ?> <span class="qcld_woo_chatbot-pro-badge"><?php esc_html_e( '(Pro Feature)', 'woowbot-woocommerce-chatbot' ); ?></span></label>
                                    <div class="qcld_woo_chatbot-radio-grid">
                                        <?php
                                        $selected_duration = get_option('qcld_woo_chatbot_video_duration', '4s');
                                        $durations = [
                                            '4s' => ['icon' => 'dashicons-clock', 'title' => esc_html__('4 Seconds', 'woowbot-woocommerce-chatbot'), 'desc' => esc_html__('Standard generation', 'woowbot-woocommerce-chatbot')],
                                            '8s' => ['icon' => 'dashicons-update-alt', 'title' => esc_html__('Extended generation', 'woowbot-woocommerce-chatbot'), 'desc' => esc_html__('Extended generation', 'woowbot-woocommerce-chatbot')]
                                        ];
                                        if(!empty($durations)){
                                            foreach ($durations as $val => $data) {
                                                $active_class = ($selected_duration === $val) ? 'active' : '';
                                                echo '<label class="qcld_woo_chatbot-radio-card ' . esc_attr($active_class) . '">';
                                                echo '<input type="radio" name="qcld_woo_chatbot_video_duration" value="'.esc_attr($val).'" ' . checked($selected_duration, $val, false) . ' disabled />';
                                                echo '<div class="qcld_woo_chatbot-radio-content">';
                                                echo '<span class="dashicons '.esc_attr($data['icon']).'"></span>';
                                                echo '<div class="qcld_woo_chatbot-radio-title">'.esc_html($data['title']).'</div>';
                                                echo '<div class="qcld_woo_chatbot-radio-desc">'.esc_html($data['desc']).'</div>';
                                                echo '</div></label>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <span class="qcld_woo_chatbot-description"><?php esc_html_e( 'Select the length of the generated video. (Available in Pro)', 'woowbot-woocommerce-chatbot' ); ?></span>
                                </div>

                                <div class="qcld_woo_chatbot-form-group qcld_woo_chatbot-full-width">
                                    <label><?php esc_html_e( 'Video Resolution', 'woowbot-woocommerce-chatbot' ); ?> <span class="qcld_woo_chatbot-pro-badge"><?php esc_html_e( '(Pro Feature)', 'woowbot-woocommerce-chatbot' ); ?></span></label>
                                    <div class="qcld_woo_chatbot-radio-grid">
                                        <?php
                                        $selected_resolution = get_option('qcld_woo_chatbot_video_resolution', '720p');
                                        $resolutions = [
                                            '720p' => ['icon' => 'dashicons-format-video', 'title' => esc_html__('720p', 'woowbot-woocommerce-chatbot'), 'desc' => esc_html__('Standard Definition', 'woowbot-woocommerce-chatbot')],
                                            '1080p' => ['icon' => 'dashicons-desktop', 'title' => esc_html__('1080p', 'woowbot-woocommerce-chatbot'), 'desc' => esc_html__('High Definition', 'woowbot-woocommerce-chatbot')]
                                        ];
                                        if(!empty($resolutions)){
                                            foreach ($resolutions as $val => $data) {
                                                $active_class = ($selected_resolution === $val) ? 'active' : '';
                                                echo '<label class="qcld_woo_chatbot-radio-card ' . esc_attr($active_class) . '">';
                                                echo '<input type="radio" name="qcld_woo_chatbot_video_resolution" value="'.esc_attr($val).'" ' . checked($selected_resolution, $val, false) . ' disabled />';
                                                echo '<div class="qcld_woo_chatbot-radio-content">';
                                                echo '<span class="dashicons '.esc_attr($data['icon']).'"></span>';
                                                echo '<div class="qcld_woo_chatbot-radio-title">'.esc_html($data['title']).'</div>';
                                                echo '<div class="qcld_woo_chatbot-radio-desc">'.esc_html($data['desc']).'</div>';
                                                echo '</div></label>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <span class="qcld_woo_chatbot-description"><?php esc_html_e( 'Select the resolution for generated videos. (Available in Pro)', 'woowbot-woocommerce-chatbot' ); ?></span>
                                </div>

                                <div class="qcld_woo_chatbot-form-group qcld_woo_chatbot-full-width">
                                    <label><?php esc_html_e( 'Image Size', 'woowbot-woocommerce-chatbot' ); ?></label>
                                    <div class="qcld_woo_chatbot-radio-grid">
                                        <?php
                                        $selected_img_size = get_option('qcld_woo_chatbot_image_size', '1024x1792');
                                        $img_sizes = [
                                            '1024x1024' => ['icon' => 'dashicons-format-image', 'title' => esc_html__('1024x1024 (Square)', 'woowbot-woocommerce-chatbot'), 'desc' => esc_html__('DALL-E 2, DALL-E 3, Gemini', 'woowbot-woocommerce-chatbot')],
                                            '1024x1792' => ['icon' => 'dashicons-smartphone', 'title' => esc_html__('1024x1792 (Portrait)', 'woowbot-woocommerce-chatbot'), 'desc' => esc_html__('DALL-E 3, Gemini', 'woowbot-woocommerce-chatbot')],
                                            '1792x1024' => ['icon' => 'dashicons-desktop', 'title' => esc_html__('1792x1024 (Landscape)', 'woowbot-woocommerce-chatbot'), 'desc' => esc_html__('DALL-E 3, Gemini', 'woowbot-woocommerce-chatbot')]
                                        ];
                                        if(!empty($img_sizes)){
                                            foreach ($img_sizes as $val => $data) {
                                                $active_class = ($selected_img_size === $val) ? 'active' : '';
                                                echo '<label class="qcld_woo_chatbot-radio-card ' . esc_attr($active_class) . '">';
                                                echo '<input type="radio" name="qcld_woo_chatbot_image_size" value="'.esc_attr($val).'" ' . checked($selected_img_size, $val, false) . ' />';
                                                echo '<div class="qcld_woo_chatbot-radio-content">';
                                                echo '<span class="dashicons '.esc_attr($data['icon']).'"></span>';
                                                echo '<div class="qcld_woo_chatbot-radio-title">'.esc_html($data['title']).'</div>';
                                                echo '<div class="qcld_woo_chatbot-radio-desc">'.esc_html($data['desc']).'</div>';
                                                echo '</div></label>';
                                            }

                                        }
                                        ?>
                                    </div>
                                    <span class="qcld_woo_chatbot-description"><?php esc_html_e( 'Select the aspect ratio / size for AI generated images.', 'woowbot-woocommerce-chatbot' ); ?></span>
                                </div>
                                
                            </div> <!-- End General Tab -->

                            

                            <div class="qcld_woo_chatbot-tab-content" id="qcld_woo_chatbot-tab-prompts">

                                <div class="qcld_woo_chatbot-form-group qcld_woo_chatbot-full-width">
                                    <label for="qcld_woo_chatbot_gemini_prompt"><?php esc_html_e( 'Google Gemini Base Prompt', 'woowbot-woocommerce-chatbot' ); ?></label>
                                    <textarea id="qcld_woo_chatbot_gemini_prompt" name="qcld_woo_chatbot_gemini_prompt" class="qcld_woo_chatbot-textarea-field" rows="3"><?php echo esc_textarea(get_option('qcld_woo_chatbot_gemini_prompt', 'Create a realistic virtual try-on image. The first image is the product to wear/use. The second image is the model/user/customer photo. Put the product on the person naturally with correct proportions, lighting, and perspective. Keep a neutral background suitable for eCommerce. Do not change the model/user/customer face appearance. Additional context: ')); ?></textarea>
                                    <div class="qcld_woo_chatbot-card-row-flex">
                                        <span class="qcld_woo_chatbot-description qcld_woo_chatbot-m-0"><?php esc_html_e( 'Customize the base instructions sent to Google Gemini. The user\'s input will be appended automatically.', 'woowbot-woocommerce-chatbot' ); ?></span>
                                        <a href="#" class="qcld_woo_chatbot-reset-prompt-btn" data-target="qcld_woo_chatbot_gemini_prompt" data-default="Create a realistic virtual try-on image. The first image is the product to wear/use. The second image is the model/user/customer photo. Put the product on the person naturally with correct proportions, lighting, and perspective. Keep a neutral background suitable for eCommerce. Do not change the model/user/customer face appearance. Additional context: "><?php esc_html_e( 'Reset to Default', 'woowbot-woocommerce-chatbot' ); ?></a>
                                    </div>
                                </div>

                                <div class="qcld_woo_chatbot-form-group qcld_woo_chatbot-full-width">
                                    <label for="qcld_woo_chatbot_veo_prompt"><?php esc_html_e( 'Gemini Veo Video Base Prompt', 'woowbot-woocommerce-chatbot' ); ?> <span class="qcld_woo_chatbot-pro-badge"><?php esc_html_e( '(Pro Feature)', 'woowbot-woocommerce-chatbot' ); ?></span></label>
                                    <textarea id="qcld_woo_chatbot_veo_prompt" name="qcld_woo_chatbot_veo_prompt" class="qcld_woo_chatbot-textarea-field" rows="3" disabled><?php echo esc_textarea(get_option('qcld_woo_chatbot_veo_prompt', 'Create a realistic virtual try-on video based on this image. The first image is the product to wear/use. The second image is the model/user/customer photo. Put the product on the person naturally with correct proportions, lighting, and perspective. Keep a neutral background suitable for eCommerce. Do not change the model/user/customer face appearance.  ')); ?></textarea>
                                    <div class="qcld_woo_chatbot-card-row-flex">
                                        <span class="qcld_woo_chatbot-description qcld_woo_chatbot-m-0"><?php esc_html_e( 'Customize the base instructions for Veo video generation. The user\'s input will be appended automatically. (Available in Pro)', 'woowbot-woocommerce-chatbot' ); ?></span>
                                        <a href="#" class="qcld_woo_chatbot-reset-prompt-btn" data-target="qcld_woo_chatbot_veo_prompt" data-default="Create a realistic virtual try-on video based on this image. The first image is the product to wear/use. The second image is the model/user/customer photo. Put the product on the person naturally with correct proportions, lighting, and perspective. Keep a neutral background suitable for eCommerce. Do not change the model/user/customer face appearance.  "><?php esc_html_e( 'Reset to Default', 'woowbot-woocommerce-chatbot' ); ?></a>
                                    </div>
                                </div>

                                <div class="qcld_woo_chatbot-form-group qcld_woo_chatbot-full-width">
                                    <label for="qcld_woo_chatbot_openai_system_prompt"><?php esc_html_e( 'OpenAI Base Prompt', 'woowbot-woocommerce-chatbot' ); ?></label>
                                    <textarea id="qcld_woo_chatbot_openai_system_prompt" name="qcld_woo_chatbot_openai_system_prompt" class="qcld_woo_chatbot-textarea-field" rows="3"><?php echo esc_textarea(get_option('qcld_woo_chatbot_openai_system_prompt', 'Create a realistic virtual try-on image. The first image is the product to wear/use. The second image is the model/user/customer photo. Put the product on the person naturally with correct proportions, lighting, and perspective. Keep a neutral background suitable for eCommerce. Do not change the model/user/customer face appearance. Additional context: ')); ?></textarea>
                                    <div class="qcld_woo_chatbot-card-row-flex">
                                        <span class="qcld_woo_chatbot-description qcld_woo_chatbot-m-0"><?php esc_html_e( 'Customize the base instructions sent to OpenAI. The user\'s input will be appended automatically.', 'woowbot-woocommerce-chatbot' ); ?></span>
                                        <a href="#" class="qcld_woo_chatbot-reset-prompt-btn" data-target="qcld_woo_chatbot_openai_system_prompt" data-default="Create a realistic virtual try-on image. The first image is the product to wear/use. The second image is the model/user/customer photo. Put the product on the person naturally with correct proportions, lighting, and perspective. Keep a neutral background suitable for eCommerce. Do not change the model/user/customer face appearance. Additional context: "><?php esc_html_e( 'Reset to Default', 'woowbot-woocommerce-chatbot' ); ?></a>
                                    </div>
                                </div>

                                <div class="qcld_woo_chatbot-form-group qcld_woo_chatbot-full-width">
                                    <label for="qcld_woo_chatbot_build_generation_prompt"><?php esc_html_e( 'Final Prompt Structure Builder', 'woowbot-woocommerce-chatbot' ); ?></label>
                                    <textarea id="qcld_woo_chatbot_build_generation_prompt" name="qcld_woo_chatbot_build_generation_prompt" class="qcld_woo_chatbot-textarea-field" rows="3"><?php echo esc_textarea(get_option('qcld_woo_chatbot_build_generation_prompt', 'Product name: {product_name}. Product Image URL: {product_image_url}. Reference Image URL: {second_image_url}. Scenario instruction: {scenario}')); ?></textarea>
                                    <div class="qcld_woo_chatbot-card-row-flex">
                                        <span class="qcld_woo_chatbot-description qcld_woo_chatbot-m-0"><?php esc_html_e( 'Use placeholders:', 'woowbot-woocommerce-chatbot' ); ?> <code>{product_name}</code>, <code>{product_image_url}</code>, <code>{second_image_url}</code>, <code>{scenario}</code> <?php esc_html_e( 'to structure how the final combined prompt is sent.', 'woowbot-woocommerce-chatbot' ); ?></span>
                                        <a href="#" class="qcld_woo_chatbot-reset-prompt-btn" data-target="qcld_woo_chatbot_build_generation_prompt" data-default="Product name: {product_name}. Product Image URL: {product_image_url}. Reference Image URL: {second_image_url}. Scenario instruction: {scenario}"><?php esc_html_e( 'Reset to Default', 'woowbot-woocommerce-chatbot' ); ?></a>
                                    </div>
                                </div>

                            </div> <!-- End Prompts Tab -->
                            
                            <div class="qcld_woo_chatbot-tab-content" id="qcld_woo_chatbot-tab-help">
                                <div class="qcld_woo_chatbot-help-grid">
                                    <div class="qcld_woo_chatbot-help-main">
                                        <div class="qcld_woo_chatbot-help-article">
                                            <h3><span class="dashicons dashicons-welcome-learn-more qcld_woo_chatbot-mt-2"></span> <?php esc_html_e( 'Quick Start Guide', 'woowbot-woocommerce-chatbot' ); ?></h3>
                                            <p><?php esc_html_e( 'Welcome to', 'woowbot-woocommerce-chatbot' ); ?> <strong><?php esc_html_e( 'WooCommerce AI Try-On', 'woowbot-woocommerce-chatbot' ); ?></strong>. <?php esc_html_e( 'This plugin allows your customers to virtually try on your products using cutting edge generative AI.', 'woowbot-woocommerce-chatbot' ); ?></p>
                                            
                                            <div class="qcld_woo_chatbot-alert-box">
                                                <strong><?php esc_html_e( 'Pro Tip:', 'woowbot-woocommerce-chatbot' ); ?></strong> <?php echo wp_kses_post( __( 'You can completely customize the "Try On" button\'s position and text in the <strong>General Settings</strong> tab!', 'woowbot-woocommerce-chatbot' ) ); ?>
                                            </div>

                                            <h4><?php esc_html_e( '1. Customer Experience', 'woowbot-woocommerce-chatbot' ); ?></h4>
                                            <p><?php echo wp_kses_post( __( 'When a customer clicks the Try-On button, a premium modal interface slides up. They can easily drag-and-drop or upload a reference photo of themselves. They also have a text box to add custom scenario instructions (e.g., <em>"Standing in a modern coffee shop"</em>).', 'woowbot-woocommerce-chatbot' ) ); ?></p>

                                            <h4><?php esc_html_e( '2. The Generation Process', 'woowbot-woocommerce-chatbot' ); ?></h4>
                                            <p><?php echo wp_kses_post( __( 'Once they click Generate, the plugin takes the <strong>Product Image</strong>, the <strong>Customer\'s Uploaded Image</strong>, and their <strong>Scenario text</strong>, and combines them using the <em>Final Prompt Structure Builder</em> (found in the Prompts tab).', 'woowbot-woocommerce-chatbot' ) ); ?></p>

                                            <h4><?php esc_html_e( '3. Image vs Video', 'woowbot-woocommerce-chatbot' ); ?></h4>
                                            <p><strong><?php esc_html_e( 'Images:', 'woowbot-woocommerce-chatbot' ); ?></strong> <?php esc_html_e( 'Generated instantly by either Google Gemini (Imagen 3) or OpenAI (DALL-E 3).', 'woowbot-woocommerce-chatbot' ); ?><br>
                                                <strong><?php esc_html_e( 'Videos:', 'woowbot-woocommerce-chatbot' ); ?></strong> <?php echo wp_kses_post( __( 'Powered exclusively by Google\'s cutting-edge <strong>Veo</strong> model. Video generation can take several minutes. The plugin handles this gracefully by continuously polling Google\'s servers in the background, allowing the customer to see real-time status updates without leaving the page.', 'woowbot-woocommerce-chatbot' ) ); ?></p>
                                            </div>

                                            <div class="qcld_woo_chatbot-help-article">
                                                <h3><span class="dashicons dashicons-admin-network qcld_woo_chatbot-mt-2"></span> <?php esc_html_e( 'API Keys & Configuration', 'woowbot-woocommerce-chatbot' ); ?></h3>
                                                <p><?php esc_html_e( 'Before the Try-On button will function, you must configure your AI providers.', 'woowbot-woocommerce-chatbot' ); ?></p>
                                                
                                                <div class="qcld_woo_chatbot-alert-box warning">
                                                    <strong><?php esc_html_e( 'Important:', 'woowbot-woocommerce-chatbot' ); ?></strong> <?php echo wp_kses_post( __( 'You must click the <strong>Verify</strong> button next to your selected models in the AI Settings tab to confirm your API key has access.', 'woowbot-woocommerce-chatbot' )); ?>
                                                </div>

                                                <h4><?php esc_html_e( 'Getting a Google Gemini Key', 'woowbot-woocommerce-chatbot' ); ?></h4>
                                                <p><?php echo wp_kses_post( __( 'Google\'s Gemini models (including Imagen 3 and Veo) require an API key from Google AI Studio. You can get a free API key by visiting <a href="https://aistudio.google.com/" target="_blank">Google AI Studio</a>.', 'woowbot-woocommerce-chatbot' )); ?></p>

                                                <h4><?php esc_html_e( 'Getting an OpenAI Key', 'woowbot-woocommerce-chatbot' ); ?></h4>
                                                <p><?php echo wp_kses_post( __( 'OpenAI\'s models (like DALL-E 3) require a paid OpenAI platform account. You can generate a key at <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI API Keys</a>.', 'woowbot-woocommerce-chatbot' )); ?></p>
                                            </div>
                                        </div>

                                        <div class="qcld_woo_chatbot-help-sidebar">
                                            <div class="qcld_woo_chatbot-help-sidebar-card">
                                                <h4><span class="dashicons dashicons-editor-help"></span> <?php esc_html_e( 'Need Support?', 'woowbot-woocommerce-chatbot' ); ?></h4>
                                                <p><?php esc_html_e( 'If you\'re experiencing issues with the plugin or have feature requests, please reach out to our support desk.', 'woowbot-woocommerce-chatbot' ); ?></p>
                                                <p class="qcld_woo_chatbot-mb-0 qcld_woo_chatbot-mt-10"><a href="#"><?php esc_html_e( 'Contact Support &rarr;', 'woowbot-woocommerce-chatbot' ); ?></a></p>
                                            </div>

                                            <div class="qcld_woo_chatbot-help-sidebar-card">
                                                <h4><span class="dashicons dashicons-book"></span> <?php esc_html_e( 'Useful Links', 'woowbot-woocommerce-chatbot' ); ?></h4>
                                                <ul class="qcld_woo_chatbot-clean-list">
                                                    <li class="qcld_woo_chatbot-mb-8"><a href="https://aistudio.google.com/" target="_blank"><?php esc_html_e( 'Google AI Studio', 'woowbot-woocommerce-chatbot' ); ?></a></li>
                                                    <li class="qcld_woo_chatbot-mb-8"><a href="https://platform.openai.com/" target="_blank"><?php esc_html_e( 'OpenAI Developer Platform', 'woowbot-woocommerce-chatbot' ); ?></a></li>
                                                    <li><a href="https://woocommerce.com/documentation/" target="_blank"><?php esc_html_e( 'WooCommerce Docs', 'woowbot-woocommerce-chatbot' ); ?></a></li>
                                                </ul>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div> <!-- End Help Tab -->

                            </div> <!-- closes qcld_woo_chatbot-card-body -->
                        </div> <!-- closes qcld_woo_chatbot-settings-card -->
                    </div> <!-- closes qcld_woo_chatbot-settings-grid -->
                    
                    <div class="qcld_woo_chatbot-settings-footer">
                        <?php submit_button(esc_html__( 'Save Configurations', 'woowbot-woocommerce-chatbot' ), 'primary', 'submit', false, array('class' => 'qcld_woo_chatbot-btn qcld_woo_chatbot-btn-save')); ?>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    add_action( 'admin_init', 'qcld_woo_chatbot_register_settings' );

    function qcld_woo_chatbot_ai_settings_page() {
        $selected_provider = get_option('qcld_woo_chatbot_api_provider', 'gemini');
        ?>
        <div class="wrap">
            <h2><?php esc_html_e( 'AI Settings', 'woowbot-woocommerce-chatbot' ); ?></h2>
            <div class="qcld_woo_chatbot-admin-wrap">
                <form method="post" action="options.php" class="qcld_woo_chatbot-settings-form">
                    <?php settings_fields( 'qcld_woo_chatbot_ai_settings_group' ); ?>
                    <div class="qcld_woo_chatbot-settings-grid">
                        <div class="qcld_woo_chatbot-settings-card">
                            <div class="qcld_woo_chatbot-card-header">
                                <h2><?php esc_html_e( 'AI API Configuration', 'woowbot-woocommerce-chatbot' ); ?></h2>
                                <p><?php esc_html_e( 'Configure API keys, models, and generation settings.', 'woowbot-woocommerce-chatbot' ); ?></p>
                            </div>
                            <div class="qcld_woo_chatbot-card-body">
                                

                                <!-- Provider Selection Section -->
                                <h3 class="qcld_woo_chatbot-section-title"><?php esc_html_e( 'Image Generation AI Provider', 'woowbot-woocommerce-chatbot' ); ?></h3>
                                <div class="qcld_woo_chatbot-form-group">
                                    <div class="qcld_woo_chatbot-ai-provider-toggle-group">
                                        <label class="qcld_woo_chatbot-ai-provider-card provider-gemini <?php echo ($selected_provider === 'gemini') ? 'active' : ''; ?>">
                                            <input type="radio" name="qcld_woo_chatbot_api_provider" value="gemini" <?php checked($selected_provider, 'gemini'); ?> />
                                            <span class="qcld_woo_chatbot-ai-provider-icon">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C12.5 7.5 16.5 11.5 22 12C16.5 12.5 12.5 16.5 12 22C11.5 16.5 7.5 12.5 2 12C7.5 11.5 11.5 7.5 12 2Z"/></svg>
                                            </span>
                                            <span class="qcld_woo_chatbot-ai-provider-title"><?php esc_html_e( 'Google Gemini', 'woowbot-woocommerce-chatbot' ); ?></span>
                                        </label>
                                        <label class="qcld_woo_chatbot-ai-provider-card provider-openai <?php echo ($selected_provider === 'openai') ? 'active' : ''; ?>">
                                            <input type="radio" name="qcld_woo_chatbot_api_provider" value="openai" <?php checked($selected_provider, 'openai'); ?> />
                                            <span class="qcld_woo_chatbot-ai-provider-icon">
                                              <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M21.74 11.45a3.83 3.83 0 0 0-.25-1.92 4 4 0 0 0-1.12-1.46 3.73 3.73 0 0 0-.58-.4l.26-.45a3.78 3.78 0 0 0-.5-4.8 3.85 3.85 0 0 0-4.82-.48l-.45.26a3.83 3.83 0 0 0-1.85-.66 3.89 3.89 0 0 0-4.04 2.37l-.27.46a3.85 3.85 0 0 0-3.32.92 3.78 3.78 0 0 0-.5 4.8l.26.45A3.83 3.83 0 0 0 4 13a3.89 3.89 0 0 0 2 3.42l-.26.46a3.79 3.79 0 0 0 .5 4.8 3.85 3.85 0 0 0 4.82.48l.45-.26a3.83 3.83 0 0 0 1.85.66c1.93.07 3.63-1.16 4.04-2.37l.27-.46a3.85 3.85 0 0 0 3.32-.92 3.78 3.78 0 0 0 .5-4.8l-.26-.45a3.81 3.81 0 0 0 .85-2.07zm-7.79 8.2l-3.37-1.94a.5.5 0 0 1-.25-.43v-4.72l1.62.93a.5.5 0 0 0 .75-.43v-2.04l3.18 1.84a.5.5 0 0 1 .25.43v3.91a2.82 2.82 0 0 1-2.18 2.48zm-5.73-2.6a2.85 2.85 0 0 1-1.07-3.08l1.62-.94a.5.5 0 0 0 .25-.43v-4.08L12 10.45v3.68a.5.5 0 0 1-.25.43zm-1.63-7.53a2.81 2.81 0 0 1 3.25-.6l3.37 1.94a.5.5 0 0 1 .25.43v1.89L13.1 11.2a.5.5 0 0 0-.75.43v2.04l-3.18-1.84a.5.5 0 0 1-.25-.43zm12.39.9l-1.62.94a.5.5 0 0 0-.25.43v4.08l-3.06-1.77v-3.68a.5.5 0 0 1 .25-.43l3.37-1.94a2.84 2.84 0 0 1 4.31 2.37zm-2.9-4.88a2.81 2.81 0 0 1-3.25.6l-3.37-1.94a.5.5 0 0 1-.25-.43v-1.89l1.62.93a.5.5 0 0 0 .75-.43v-2.04l3.18 1.84a.5.5 0 0 1 .25.43zM12 11.08l1.69-.97 1.69.97v1.95L13.69 14 12 13.03z"/></svg>
                                          </span>
                                          <span class="qcld_woo_chatbot-ai-provider-title"><?php esc_html_e( 'OpenAI', 'woowbot-woocommerce-chatbot' ); ?></span>
                                      </label>
                                  </div>
                              </div>

                              <!-- OpenAI Settings Section -->
                              <div class="qcld_woo_chatbot-openai-settings-row">
                                <hr class="qcld_woo_chatbot-divider" />
                                <h3 class="qcld_woo_chatbot-section-title qcld_woo_chatbot-header-openai"><?php esc_html_e( 'OpenAI Integration Settings', 'woowbot-woocommerce-chatbot' ); ?></h3>
                                
                                <div class="qcld_woo_chatbot-form-group">
                                    <label for="qcld_woo_chatbot_openai_api_key"><?php esc_html_e( 'OpenAI API Key', 'woowbot-woocommerce-chatbot' ); ?></label>
                                    <div class="qcld_woo_chatbot-input-action-group">

                                        <?php

                                        $qcld_woo_chatbot_openai_api_key = get_option('qcld_woo_chatbot_openai_api_key');
                                        if (class_exists('QCLD_Woo_Chatbot') && empty($qcld_woo_chatbot_openai_api_key) ) {
                                            $qcld_open_ai_api_key = get_option('qcld_open_ai_api_key');
                                            if(!empty($qcld_open_ai_api_key)){
                                                $qcld_woo_chatbot_openai_api_key = get_option('qcld_open_ai_api_key');
                                                update_option('qcld_woo_chatbot_openai_api_key', $qcld_woo_chatbot_openai_api_key );
                                            }
                                        }

                                        ?>
                                        <input type="password" id="qcld_woo_chatbot_openai_api_key" name="qcld_woo_chatbot_openai_api_key" class="qcld_woo_chatbot-input-field" value="<?php echo esc_attr(get_option('qcld_woo_chatbot_openai_api_key')); ?>" />
                                        <button type="button" class="qcld_woo_chatbot-btn qcld_woo_chatbot-btn-primary" id="qcld_woo_chatbot_test_openai_btn"><?php esc_html_e( 'Test Connection', 'woowbot-woocommerce-chatbot' ); ?></button>
                                    </div>
                                    <span id="qcld_woo_chatbot_openai_test_status" class="qcld_woo_chatbot-status-text"></span>
                                </div>
                                
                                <div class="qcld_woo_chatbot-form-group">
                                    <label for="qcld_woo_chatbot_openai_image_model"><?php esc_html_e( 'OpenAI Image Model', 'woowbot-woocommerce-chatbot' ); ?></label>
                                    <div class="qcld_woo_chatbot-input-action-group">
                                        <select id="qcld_woo_chatbot_openai_image_model" name="qcld_woo_chatbot_openai_image_model" class="qcld_woo_chatbot-select-field">
                                            <?php 
                                            $saved_model = get_option('qcld_woo_chatbot_openai_image_model', 'dall-e-3');
                                            $image_models = get_option('qcld_woo_chatbot_openai_available_image_models', array());
                                            if (!is_array($image_models)) $image_models = array();
                                            if (empty($image_models)) $image_models = array($saved_model);
                                            if (!in_array($saved_model, $image_models)) array_unshift($image_models, $saved_model);
                                            
                                            if(!empty($image_models)){
                                                foreach ($image_models as $m) {
                                                    $selected = ($m === $saved_model) ? 'selected' : '';
                                                    echo '<option value="' . esc_attr($m) . '" ' . wp_kses_post($selected) . '>' . esc_html($m) . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                        <button type="button" class="qcld_woo_chatbot-btn qcld_woo_chatbot-btn-secondary qcld_woo_chatbot-verify-model-btn" data-provider="openai" data-type="image"><?php esc_html_e( 'Verify', 'woowbot-woocommerce-chatbot' ); ?></button>
                                    </div>
                                    <span class="qcld_woo_chatbot-verify-status qcld_woo_chatbot-status-text"></span>
                                    <span class="qcld_woo_chatbot-description"><?php esc_html_e( 'Select the OpenAI model for image generation.', 'woowbot-woocommerce-chatbot' ); ?></span>
                                </div>
                            </div>

                            <!-- Gemini Settings Section -->
                            <div class="qcld_woo_chatbot-gemini-settings-row">
                                <hr class="qcld_woo_chatbot-divider" />
                                <div class="qcld_woo_chatbot-section-header-flex">
                                    <h3 class="qcld_woo_chatbot-section-title qcld_woo_chatbot-header-gemini qcld_woo_chatbot-mb-0"><?php esc_html_e( 'Google Gemini Integration Settings', 'woowbot-woocommerce-chatbot' ); ?></h3>
                                    <span class="qcld_woo_chatbot-badge"><?php esc_html_e( 'Powered by Veo for Video', 'woowbot-woocommerce-chatbot' ); ?></span>
                                </div>
                                
                                <div class="qcld_woo_chatbot-form-group">
                                    <label for="qcld_woo_chatbot_gemini_api_key"><?php esc_html_e( 'Gemini API Key', 'woowbot-woocommerce-chatbot' ); ?></label>

                                    <?php 

                                    $qcld_woo_chatbot_gemini_api_key = get_option('qcld_woo_chatbot_gemini_api_key');
                                    if (class_exists('QCLD_Woo_Chatbot') && empty($qcld_woo_chatbot_gemini_api_key) ) {
                                        $qcld_gemini_api_key = get_option('qcld_gemini_api_key');
                                        if(!empty($qcld_gemini_api_key)){
                                            $qcld_woo_chatbot_gemini_api_key = get_option('qcld_gemini_api_key');
                                            update_option('qcld_woo_chatbot_gemini_api_key', $qcld_woo_chatbot_gemini_api_key );
                                        }
                                    }

                                    ?>
                                    <div class="qcld_woo_chatbot-input-action-group">
                                        <input type="password" id="qcld_woo_chatbot_gemini_api_key" name="qcld_woo_chatbot_gemini_api_key" class="qcld_woo_chatbot-input-field" value="<?php echo esc_attr(get_option('qcld_woo_chatbot_gemini_api_key')); ?>" />
                                        <button type="button" class="qcld_woo_chatbot-btn qcld_woo_chatbot-btn-primary" id="qcld_woo_chatbot_test_gemini_btn"><?php esc_html_e( 'Test Connection', 'woowbot-woocommerce-chatbot' ); ?></button>
                                    </div>
                                    <span id="qcld_woo_chatbot_gemini_test_status" class="qcld_woo_chatbot-status-text"></span>
                                </div>
                                
                                <div class="qcld_woo_chatbot-form-group qcld_woo_chatbot-gemini-image-row">
                                    <label for="qcld_woo_chatbot_gemini_image_model"><?php esc_html_e( 'Gemini Image Model', 'woowbot-woocommerce-chatbot' ); ?></label>
                                    <div class="qcld_woo_chatbot-input-action-group">
                                        <select id="qcld_woo_chatbot_gemini_image_model" name="qcld_woo_chatbot_gemini_image_model" class="qcld_woo_chatbot-select-field">
                                            <?php 
                                            $saved_g_image = get_option('qcld_woo_chatbot_gemini_image_model', 'gemini-2.5-flash-image');
                                            $g_image_models = get_option('qcld_woo_chatbot_gemini_available_image_models', array());
                                            if (!is_array($g_image_models)) $g_image_models = array();
                                            if (empty($g_image_models)) $g_image_models = array($saved_g_image);
                                            if (!in_array($saved_g_image, $g_image_models)) array_unshift($g_image_models, $saved_g_image);

                                            if(!empty($g_image_models)){
                                                foreach ($g_image_models as $m) {
                                                    $selected = ($m === $saved_g_image) ? 'selected' : '';
                                                    echo '<option value="' . esc_attr($m) . '" ' . wp_kses_post($selected) . '>' . esc_html($m) . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                        <button type="button" class="qcld_woo_chatbot-btn qcld_woo_chatbot-btn-secondary qcld_woo_chatbot-verify-model-btn" data-provider="gemini" data-type="image"><?php esc_html_e( 'Verify', 'woowbot-woocommerce-chatbot' ); ?></button>
                                    </div>
                                    <span class="qcld_woo_chatbot-verify-status qcld_woo_chatbot-status-text"></span>
                                    <span class="qcld_woo_chatbot-description"><?php esc_html_e( 'Select the Gemini model for image generation.', 'woowbot-woocommerce-chatbot' ); ?></span>
                                </div>

                                <div class="qcld_woo_chatbot-form-group">
                                    <label for="qcld_woo_chatbot_gemini_video_model"><?php esc_html_e( 'Gemini Video Model (Google Veo)', 'woowbot-woocommerce-chatbot' ); ?> <span class="qcld_woo_chatbot-pro-badge"><?php esc_html_e( '(Pro Feature)', 'woowbot-woocommerce-chatbot' ); ?></span></label>
                                    <div class="qcld_woo_chatbot-input-action-group">
                                        <select id="qcld_woo_chatbot_gemini_video_model" name="qcld_woo_chatbot_gemini_video_model" class="qcld_woo_chatbot-select-field" disabled>
                                            <?php
                                            $saved_g_video = get_option('qcld_woo_chatbot_gemini_video_model', 'veo-3.1-generate-preview');
                                            $g_video_models = get_option('qcld_woo_chatbot_gemini_available_video_models', array());
                                            if (!is_array($g_video_models)) $g_video_models = array();
                                            if (empty($g_video_models)) $g_video_models = array($saved_g_video);
                                            if (!in_array($saved_g_video, $g_video_models)) array_unshift($g_video_models, $saved_g_video);
                                            
                                            if(!empty($g_video_models)){
                                                foreach ($g_video_models as $m) {
                                                    $selected = ($m === $saved_g_video) ? 'selected' : '';
                                                    $label = esc_html($m);
                                                    if ($m === 'veo-3.1-fast-generate-preview') {
                                                        $label .= ' - $0.15/sec ' . esc_html__(' ( Recommended )', 'woowbot-woocommerce-chatbot');
                                                    } elseif ($m === 'veo-3.1-generate-preview') {
                                                        $label .= ' - $0.40/sec';
                                                    } elseif ($m === 'veo-3.1-lite-generate-preview') {
                                                        $label .= ' - $0.05/sec';
                                                    } elseif (strpos($m, 'veo-2.0-generate') !== false) {
                                                        $label .= ' - $0.40/sec';
                                                    }
                                                    echo '<option value="' . esc_attr($m) . '" ' . wp_kses_post($selected) . '>' . esc_attr($label) . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                        <button type="button" class="qcld_woo_chatbot-btn qcld_woo_chatbot-btn-secondary qcld_woo_chatbot-verify-model-btn" data-provider="gemini" data-type="video" disabled><?php esc_html_e( 'Verify', 'woowbot-woocommerce-chatbot' ); ?></button>
                                    </div>
                                    <span class="qcld_woo_chatbot-verify-status qcld_woo_chatbot-status-text"></span>
                                    <span class="qcld_woo_chatbot-description"><?php esc_html_e( 'Select the Google Veo model for video try-on generation. (Available in Pro)', 'woowbot-woocommerce-chatbot' ); ?></span>
                                </div>

                            </div>

                            
                        </div>
                    </div>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
    <?php
}

function qcld_woo_chatbot_register_settings() {
    // General & Prompts Settings
    register_setting( 'qcld_woo_chatbot_settings_group', 'qcld_woo_chatbot_button_enabled', array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'qcld_woo_chatbot_settings_group', 'qcld_woo_chatbot_logged_in_users_only', array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'qcld_woo_chatbot_settings_group', 'qcld_woo_chatbot_modal_add_to_cart_btn_enabled', array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'qcld_woo_chatbot_settings_group', 'qcld_woo_chatbot_generation_limit', array( 'sanitize_callback' => 'absint' ) );
    register_setting( 'qcld_woo_chatbot_settings_group', 'qcld_woo_chatbot_button_position', array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'qcld_woo_chatbot_settings_group', 'qcld_woo_chatbot_button_icon', array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'qcld_woo_chatbot_settings_group', 'qcld_woo_chatbot_button_text', array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'qcld_woo_chatbot_settings_group', 'qcld_woo_chatbot_build_generation_prompt', array( 'sanitize_callback' => 'sanitize_textarea_field' ) );
    register_setting( 'qcld_woo_chatbot_settings_group', 'qcld_woo_chatbot_gemini_prompt', array( 'sanitize_callback' => 'sanitize_textarea_field' ) );
    register_setting( 'qcld_woo_chatbot_settings_group', 'qcld_woo_chatbot_openai_system_prompt', array( 'sanitize_callback' => 'sanitize_textarea_field' ) );
    register_setting( 'qcld_woo_chatbot_settings_group', 'qcld_woo_chatbot_image_size', array( 'sanitize_callback' => 'sanitize_text_field' ) );

    // AI Configuration Settings
    register_setting( 'qcld_woo_chatbot_ai_settings_group', 'qcld_woo_chatbot_api_provider', array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'qcld_woo_chatbot_ai_settings_group', 'qcld_woo_chatbot_openai_api_key', array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'qcld_woo_chatbot_ai_settings_group', 'qcld_woo_chatbot_openai_image_model', array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'qcld_woo_chatbot_ai_settings_group', 'qcld_woo_chatbot_gemini_api_key', array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'qcld_woo_chatbot_ai_settings_group', 'qcld_woo_chatbot_gemini_image_model', array( 'sanitize_callback' => 'sanitize_text_field' ) );

}

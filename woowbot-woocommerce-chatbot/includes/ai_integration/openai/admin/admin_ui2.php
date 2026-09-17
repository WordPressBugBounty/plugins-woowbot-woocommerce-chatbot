<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
                <div class="card-body p-sm-0">
                    <!-- <div class="alert alert-danger" role="alert">
                        <?php // echo esc_html__('OpenAI has disabled some of the older models. Please use GPT 3.5 or 4 to Fine tune. You need to update the dataset and Fine tune again. Please check the Help section for details.', 'woowbot-woocommerce-chatbot'); ?>
                    </div> -->
                    <ul class="nav nav-tabs">
                        <li ><a class="active" data-toggle="tab" href="#wp-chatbot-openai-settings"><span class="wpwbot-admin-tab-icon "> <i class="dashicons dashicons-admin-generic"></i> </span><?php echo esc_html__('OpenAI settings', 'woowbot-woocommerce-chatbot'); ?></a></li>
                        <li><a data-toggle="tab" href="#wp-chatbot-openai-rag"><span class="wpwbot-admin-tab-icon "> <i class="dashicons dashicons-admin-generic"></i> </span><?php echo esc_html__('RAG', 'woowbot-woocommerce-chatbot'); ?></a></li>
                  
                        <li><a data-toggle="tab" href="#wp-chatbot-img_generator"><span class="wpwbot-admin-tab-icon "> <i class="dashicons dashicons-format-gallery"></i> </span><?php echo esc_html__('AI Image Generator', 'woowbot-woocommerce-chatbot'); ?></a></li>
                        <li><a data-toggle="tab" href="#wp-chatbot-content_writer"><span class="wpwbot-admin-tab-icon "> <i class="dashicons dashicons-format-status"></i> </span><?php echo esc_html__('AI Article Generator', 'woowbot-woocommerce-chatbot'); ?></a></li>
                        <li><a data-toggle="tab" href="#wp-chatbot-openai-help"><span class="wpwbot-admin-tab-icon "> <i class="dashicons dashicons-editor-help"></i> </span><?php echo esc_html__('Help', 'woowbot-woocommerce-chatbot'); ?></a></li>
                    </ul>
                 
                    <div class="qcld-tab-content-main">
                    <div class="tab-content">
                        <div id="wp-chatbot-openai-settings" class="tab-pane in active">
                            <?php require_once(QCLD_WOOCHATBOT_PLUGIN_DIR_FULL_PATH . 'includes/ai_integration/openai/admin/settings.php'); ?>
                        </div>
                        <div id="wp-chatbot-openai-rag" class="tab-pane">
                            <?php 
                                require_once(QCLD_WOOCHATBOT_PLUGIN_DIR_FULL_PATH . 'includes/ai_integration/openai/admin/openai-rag.php');
                            ?>
                        </div>
                        <div id="wp-chatbot-img_generator" class="tab-pane">
                            <div class="row">
                                <div class="col-xs-12">
                                    <?php  require_once(QCLD_WOOCHATBOT_PLUGIN_DIR_FULL_PATH . 'includes/ai_integration/openai/admin/img_generator.php' ); ?>
                                </div>
                            </div>
                        </div>
                        <div id="wp-chatbot-content_writer" class="tab-pane">
                            <div class="row">
                                <div class="col-xs-12">
                                    <?php  require_once(QCLD_WOOCHATBOT_PLUGIN_DIR_FULL_PATH . 'includes/ai_integration/openai/admin/content_writer.php' ); ?>
                                </div>
                            </div>
                        </div>
                        <div id="wp-chatbot-openai-help" class="tab-pane">
                            <?php  require_once(QCLD_WOOCHATBOT_PLUGIN_DIR_FULL_PATH . 'includes/ai_integration/openai/admin/help.php' ); ?>
                        </div>
                    </div>

                    <div class="qcld-tab-content-right-into">

                        <div class="qcld-tab-content-right-into-box">

                        <div class="wp-chatbot-admingradient-color">
                            <img class="wp-chatbot-admin-banner" src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/template-sample.png' ); ?>" alt="">
                                <h3 class="wp-chatbot-admincart-title"><?php esc_html_e( 'Upgrade To', 'woowbot-woocommerce-chatbot' ); ?> <span><?php esc_html_e( 'Pro', 'woowbot-woocommerce-chatbot' ); ?></span></h3>
                                <ul class="feature-list">

                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Core ChatBot Pro', 'woowbot-woocommerce-chatbot' ); ?></li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'WooCommerce module', 'woowbot-woocommerce-chatbot' ); ?></li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Chat Sessions and Histories', 'woowbot-woocommerce-chatbot' ); ?></li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Extended Search Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Simple text Responses Pro Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Conversational Forms Pro Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'OpenAI Pro Adv.(Training, Fine Tuning, Assistant)', 'woowbot-woocommerce-chatbot' ); ?></li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Live (Human) Chat Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Tavily Search API module', 'woowbot-woocommerce-chatbot' ); ?></li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Extended UI Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'WebHook & Mailing List Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'White Label Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'FaceBook Messenger Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'WhatsApp through Twilio Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Multi Language Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Voice Message Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Telegram Module', 'woowbot-woocommerce-chatbot' ); ?></li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt=""><?php esc_html_e( 'Priority Technical Support', 'woowbot-woocommerce-chatbot' ); ?></li>

                                </ul>

                            <a class="wp-chatbot-admin-pro-upgrade-button" target="_blank" href="<?php echo esc_url( 'https://woowbot.pro/pricing/');?>"><?php esc_html_e( 'Upgrade to Pro', 'woowbot-woocommerce-chatbot' ); ?> <img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/external-white.svg' ); ?>" alt=""></a>
                        </div>

                        </div>

                    </div>

                    </div>

                </div>
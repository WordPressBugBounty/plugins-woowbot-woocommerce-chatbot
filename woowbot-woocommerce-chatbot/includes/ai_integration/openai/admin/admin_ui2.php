<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
                <div class="card-body p-sm-0">
                    <!-- <div class="alert alert-danger" role="alert">
                        <?php // echo esc_html__('OpenAI has disabled some of the older models. Please use GPT 3.5 or 4 to Fine tune. You need to update the dataset and Fine tune again. Please check the Help section for details.', 'woowbot-woocommerce-chatbot'); ?>
                    </div> -->
                    <ul class="nav nav-tabs">
                        <li ><a class="active" data-toggle="tab" href="#wp-chatbot-openai-settings"><span class="wpwbot-admin-tab-icon "> <i class="dashicons dashicons-admin-generic"></i> </span><?php echo esc_html__('OpenAI settings', 'woowbot-woocommerce-chatbot'); ?></a></li>
                        <li><a data-toggle="tab" href="#wp-chatbot-openai-rag"><span class="wpwbot-admin-tab-icon "> <i class="dashicons dashicons-admin-generic"></i> </span><?php echo esc_html__('RAG', 'woowbot-woocommerce-chatbot'); ?></a></li>
                        <li><a data-toggle="tab" href="#wp-chatbot-openai-training-model"><span class="wpwbot-admin-tab-icon "> <i class="dashicons dashicons-plugins-checked"></i> </span><?php echo esc_html__('Training Model', 'woowbot-woocommerce-chatbot'); ?></a></li>
                        <li><a data-toggle="tab" href="#wp-chatbot-openai-assistants"><span class="wpwbot-admin-tab-icon "> <i class="dashicons dashicons-admin-home"></i> </span><?php echo esc_html__('GPT Assistant', 'woowbot-woocommerce-chatbot'); ?></a></li>
                        <li><a data-toggle="tab" href="#wp-chatbot-data_post_converter"><span class="wpwbot-admin-tab-icon "> <i class="dashicons dashicons-database-add"></i> </span><?php echo esc_html__('Fine Tune with Website Data', 'woowbot-woocommerce-chatbot'); ?></a></li>
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
                        <div id="wp-chatbot-openai-training-model" class="tab-pane">
                            <?php 
                              require_once(QCLD_WOOCHATBOT_PLUGIN_DIR_FULL_PATH . 'includes/ai_integration/openai/admin/files.php');
                            ?>
                        </div>
                        <div id="wp-chatbot-openai-assistants" class="tab-pane">
                            <?php 
                               require_once(QCLD_WOOCHATBOT_PLUGIN_DIR_FULL_PATH . 'includes/ai_integration/openai/admin/assistant.php');
                            ?>
                        </div>
                        <div id="wp-chatbot-data_post_converter" class="tab-pane">
                            <?php 
                            require_once(QCLD_WOOCHATBOT_PLUGIN_DIR_FULL_PATH . 'includes/ai_integration/openai/admin/data_post_converter.php');
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
                                <h3 class="wp-chatbot-admincart-title">Upgrade To <span>Pro</span></h3>
                                <ul class="feature-list">

                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt="">Core ChatBot Pro</li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt="">WooCommerce module</li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt="">Chat Sessions and Histories</li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt="">Extended Search Module</li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt="">Simple text Responses Pro Module</li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt="">Conversational Forms Pro Module</li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt="">OpenAI Pro Adv.(Training, Fine Tuning, Assistant)</li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt="">Live (Human) Chat Module</li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt="">Tavily Search API module</li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt="">Extended UI Module</li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt="">WebHook & Mailing List Module</li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt="">White Label Module</li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt="">FaceBook Messenger Module</li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt="">WhatsApp through Twilio Module</li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt="">Multi Language Module</li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt="">Voice Message Module</li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt="">Telegram Module</li>
                                    <li><img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/check2.svg' ); ?>" alt="">Priority Technical Support</li>

                                </ul>

                            <a class="wp-chatbot-admin-pro-upgrade-button" target="_blank" href="https://woowbot.pro/pricing/">Upgrade to Pro <img src="<?php echo esc_url( QCLD_WOOCHATBOT_PLUGIN_URL . '/images/external-white.svg' ); ?>"" alt=""></a>
                        </div>

                        </div>

                    </div>

                    </div>

                </div>
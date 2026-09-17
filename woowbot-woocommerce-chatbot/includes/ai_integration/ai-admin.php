<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$no_ai_active = (
	get_option( 'qcld_openai_enabled' ) != 1 &&
	get_option( 'qcld_gemini_enabled' ) != 1 
);
$wizard_done = ( get_option( 'wpbot_ai_setup_wizard_done' ) == 1 );

$show_wizard_automatically = $no_ai_active;
require_once QCLD_WOOCHATBOT_PLUGIN_DIR_FULL_PATH . '/includes/admin/templates/wizard-popup.php';

?>
<div class="wrap">
    <h1><?php esc_html_e( 'AI Settings', 'woowbot-woocommerce-chatbot');?></h1>
<div class="qcl-openai">
    <div class=" gx-0">

            <div class="admin-maxwith  qcld-openai-main-box">
                <div class="border-0">
                    <div class="row">
              
                        <div class="col-auto me-auto ai-settings-title-container">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=chatbot_ai_setting' ) ); ?>"><h4><?php esc_html_e( 'AI Settings', 'woowbot-woocommerce-chatbot');?></h4></a>
                            <div class="qcld-ai-settings-actions">
                                <button id="wpbot-trigger-wizard" class="qcld-btn-primary"><?php esc_html_e( 'AI Wizard', 'woowbot-woocommerce-chatbot' ); ?></button>
                                <button id="ai-knowledge-base-tab" class="qcld-btn-primary" link="page=chatbot_ai_setting#ai-knowledge-base-tab"><?php esc_html_e( 'Knowledge Base (RAG)', 'woowbot-woocommerce-chatbot' ); ?></button>
                            </div>
                            <!-- <div class="col-auto ai-settings-title-container">
                                <button id="qcld-common-ai-settings" class="qcld-btn-primary" link="page=chatbot_ai_setting#common-ai-settings-tab"><?php // esc_html_e( 'Common AI Settings', 'woowbot-woocommerce-chatbot' ); ?></button>
                            </div> -->
                        </div>
                    </div>
                </div>

                <div id="ai-provider-selector" class="qcld-ai-provider-grid" role="tablist" aria-label="<?php esc_attr_e( 'AI provider', 'woowbot-woocommerce-chatbot' ); ?>">
                    <button type="button" class="qcld-ai-provider-card <?php echo ((get_option( 'qcld_openai_enabled') == 1) || (get_option( 'qcld_openai_enabled') != 1 && get_option( 'qcld_gemini_enabled') != 1)) ? 'active' : ''; ?>" data-provider="openai" role="tab" aria-selected="<?php echo ((get_option( 'qcld_openai_enabled') == 1) || (get_option( 'qcld_openai_enabled') != 1 && get_option( 'qcld_gemini_enabled') != 1)) ? 'true' : 'false'; ?>">
                        <span class="qcld-ai-provider-icon qcld-ai-provider-icon-openai" aria-hidden="true">
                            <svg viewBox="0 0 24 24" focusable="false"><path d="M12 3.3a4.1 4.1 0 0 1 7 3v1.1a4.1 4.1 0 0 1 1.3 7.7l-1 .6a4.1 4.1 0 0 1-5.7 5.2l-1-.6a4.1 4.1 0 0 1-7-3v-1.1a4.1 4.1 0 0 1-1.3-7.7l1-.6A4.1 4.1 0 0 1 11 2.7l1 .6Zm0 2.1-4.4 2.5v5.2l2 1.2v-4.2l4.4-2.5-2-1.2Zm3.8 3.3-4.4 2.5v5.1l2 1.2v-4.1l4.4-2.6v-2.3l-2 .2Zm-8.2 1-2 1.1v5.1l4.5 2.6 2-1.2-4.5-2.6v-5Zm10.8 3.6-2 1.2v5.1l-2 1.1a2 2 0 0 0 2.9-1.8v-1.1l1-.6a2 2 0 0 0 .1-3.9Z"/></svg>
                        </span>
                        <span><?php esc_html_e( 'OpenAI', 'woowbot-woocommerce-chatbot' ); ?></span>
                        <span class="qcld-ai-provider-check" aria-hidden="true">✓</span>
                    </button>
                    <button type="button" class="qcld-ai-provider-card <?php echo (get_option( 'qcld_gemini_enabled') == 1) ? 'active' : ''; ?>" data-provider="gemini" role="tab" aria-selected="<?php echo (get_option( 'qcld_gemini_enabled') == 1) ? 'true' : 'false'; ?>">
                        <span class="qcld-ai-provider-icon qcld-ai-provider-icon-gemini" aria-hidden="true">
                            <svg viewBox="0 0 24 24" focusable="false"><path d="M12 2c.7 5.4 4.6 9.3 10 10-5.4.7-9.3 4.6-10 10-.7-5.4-4.6-9.3-10-10 5.4-.7 9.3-4.6 10-10Z"/></svg>
                        </span>
                        <span><?php esc_html_e( 'Google Gemini', 'woowbot-woocommerce-chatbot' ); ?></span>
                        <span class="qcld-ai-provider-check" aria-hidden="true">✓</span>
                    </button>
                </div>
				
                <div id="openai-settings" class="ai-settings-provider <?php echo ((get_option( 'qcld_openai_enabled') == 1)  || (get_option( 'qcld_openai_enabled') != 1 && get_option( 'qcld_gemini_enabled') != 1)) ? 'active' : ''; ?>">
                    <?php require_once(QCLD_WOOCHATBOT_PLUGIN_DIR_FULL_PATH . 'includes/ai_integration/openai/admin/admin_ui2.php'); ?>
                </div>
                
                <div id="gemini-settings" class="ai-settings-provider <?php echo (get_option( 'qcld_gemini_enabled') == 1) ? 'active' : ''; ?>">
                    <?php require_once(QCLD_WOOCHATBOT_PLUGIN_DIR_FULL_PATH . 'includes/ai_integration/gemini/admin/settings.php'); ?>
                </div>
                <div id="rag-settings" class="ai-settings-provider">
					<?php require_once QCLD_WOOCHATBOT_PLUGIN_DIR_FULL_PATH . 'includes/admin/templates/rag.php'; ?>
				</div>
                <div id="common-ai-settings" class="ai-settings-provider">
                    <?php require_once QCLD_WOOCHATBOT_PLUGIN_DIR_FULL_PATH . 'includes/admin/templates/common-ai-settings.php'; ?>
                </div>
                <div class="card-footer bg-dark text-white py-sm-4 border-0"></div>
            </div>



    </div>
</div>
</div>





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
<div class="qcl-openai">
    <div class=" gx-0">

            <div class="admin-maxwith  qcld-openai-main-box">
                <div class="border-0">
                    <div class="row">
              
                        <div class="col-auto me-auto ai-settings-title-container">
                         
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=chatbot_ai_setting' ) ); ?>"><h4><?php esc_html_e( 'AI Settings','chatbot');?></h4></a> 
                            <div class="qcld-ai-settings-top-rght">
                            <select id="ai-provider-selector" class="form-select ai-settings-selector">
                                <option value="openai" <?php echo (get_option( 'qcld_openai_enabled') == 1) ? esc_attr( 'selected') :'';?> ><?php echo esc_html__( 'OpenAI','chatbot')?></option>
                                <option value="gemini" <?php echo (get_option( 'qcld_gemini_enabled') == 1) ? esc_attr( 'selected') :'';?> ><?php echo esc_html__( 'Gemini','chatbot')?></option>
                            </select>
                            <div class="col-auto ai-settings-title-container">
                                <button id="ai-knowledge-base-tab" class="qcld-btn-primary" link="page=chatbot_ai_setting#ai-knowledge-base-tab"><?php esc_html_e( 'Knowledge Base (RAG)', 'woowbot-woocommerce-chatbot' ); ?></button>     
                            </div>
                            </div>
                            <div class="col-auto ai-settings-title-container">
                                <button id="wpbot-trigger-wizard" class="qcld-btn-primary"><?php esc_html_e( 'AI Wizard', 'woowbot-woocommerce-chatbot' ); ?></button>
                            </div>
                            <!-- <div class="col-auto ai-settings-title-container">
                                <button id="qcld-common-ai-settings" class="qcld-btn-primary" link="page=chatbot_ai_setting#common-ai-settings-tab"><?php // esc_html_e( 'Common AI Settings', 'woowbot-woocommerce-chatbot' ); ?></button>
                            </div> -->
                        </div>
    

                    </div>
                </div>
				
                <div id="openai-settings" class="ai-settings-provider" <?php echo (get_option( 'qcld_openai_enabled') == 1) ? 'style="display: block;"' :'style="display: block;"';?> >
                    <?php require_once(QCLD_WOOCHATBOT_PLUGIN_DIR_FULL_PATH . 'includes/ai_integration/openai/admin/admin_ui2.php'); ?>
                </div>
                
                <div id="gemini-settings" class="ai-settings-provider" <?php echo (get_option( 'qcld_gemini_enabled') == 1) ? 'style="display: block;"' :'style="display: none;"';?> >
                    <?php require_once(QCLD_WOOCHATBOT_PLUGIN_DIR_FULL_PATH . 'includes/ai_integration/gemini/admin/settings.php'); ?>
                </div>
                <div id="rag-settings" class="ai-settings-provider" style="display: none;">
					<?php require_once QCLD_WOOCHATBOT_PLUGIN_DIR_FULL_PATH . 'includes/admin/templates/rag.php'; ?>
				</div>
                <div id="common-ai-settings" class="ai-settings-provider" style="display: none;">
                    <?php require_once QCLD_WOOCHATBOT_PLUGIN_DIR_FULL_PATH . 'includes/admin/templates/common-ai-settings.php'; ?>
                </div>
                <div class="card-footer bg-dark text-white py-sm-4 border-0"></div>
            </div>



    </div>
</div>
</div>  




<style>

div#promotion-wpchatbot {
    margin: 0;
    padding: 0;
    border: none;
    max-width: initial !important;
    padding: 0 !important;
    margin: 20px 20px 20px 0 !important;
    padding: 15px 15px 15px 0 !important;
    border: none !important;
    border-radius: 6px !important;
    box-shadow: 0px 4px 6px 1px #ebebeb !important;
}


.qc-review-notice{
    max-width: initial !important;
    padding: 0 !important;
    margin: 20px 20px 20px 0 !important;
    padding: 15px 15px 15px 0 !important;
    border: none !important;
    border-radius: 6px !important;
    box-shadow: 0px 4px 6px 1px #ebebeb !important;
    background: #fff;
    color: #000;
}

.qc-review-text h3 {
    color: #000000;
}
.qc-review-text p {
    color: #000000;
}
</style>





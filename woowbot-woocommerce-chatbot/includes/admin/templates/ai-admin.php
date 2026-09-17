<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$no_ai_active = (
	get_option( 'ai_enabled' ) != 1 &&
	get_option( 'qcld_openrouter_enabled' ) != 1 &&
	get_option( 'qcld_gemini_enabled' ) != 1 &&
	get_option( 'qcld_grok_enabled' ) != 1
);
$wizard_done = ( get_option( 'wpbot_ai_setup_wizard_done' ) == 1 );

$show_wizard_automatically = $no_ai_active;
require_once QCLD_wpCHATBOT_PLUGIN_DIR_PATH . 'includes/admin/templates/wizard-popup.php';
?>
<div class="wrap qcld-main-wrapper">
    <div class="qcld-wp-chatbot-wrap-header-aisection">
<div class="qcld-wp-chatbot-wrap-header">

    <div class="qcld-wp-chatbot-wrap-header-logo"><a href="#" class="qcld-wp-chatbot-wrap-site__logo"><img class="qcld-header-logo-img" src="<?php echo esc_url( QCLD_wpCHATBOT_IMG_URL . '/chatbot.png' ); ?>" alt="Dialogflow CX"> <?php esc_html_e( 'WoowbotControl Panel', 'woowbot-woocommerce-chatbot');?> </a>
    <p><strong><?php esc_html_e( 'Core Version:', 'woowbot-woocommerce-chatbot');?></strong> v<?php echo esc_html( QCLD_wpCHATBOT_VERSION ); ?></p>
    </div>
    <ul class="qcld-wp-chatbot-wrap-version-wrapper">
        <li>
     <a class="wpchatbot-Upgrade" href="<?php echo esc_url( 'https://www.woowbot.pro/');?>" target="_blank"><?php esc_html_e( 'Upgrade To Pro', 'woowbot-woocommerce-chatbot');?></a> 
      
      </li>
	  </ul>
</div>
    </div>
</div>

<div class="qcl-openai">
    <div class="row gx-0">

            <div class="admin-maxwith qcld-openai-main-box">
                <div class="border-0">
                    <div class="row">
              
                        <div class="col-auto me-auto ai-settings-title-container">
                         
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=chatbot_ai_setting' ) ); ?>"><h4><?php esc_html_e( 'AI Settings', 'woowbot-woocommerce-chatbot');?></h4></a> 
                            <div class="qcld-ai-settings-top-rght">
                            <select id="ai-provider-selector" class="form-select ai-settings-selector">
                                <option value="openai" <?php echo (get_option( 'qcld_openai_enabled') == 1) ? esc_attr( 'selected') :'';?> ><?php echo esc_html__( 'OpenAI', 'woowbot-woocommerce-chatbot')?></option>
                                <option value="openrouter" <?php echo (get_option( 'qcld_openrouter_enabled') == 1) ? esc_attr( 'selected') :'';?> ><?php echo esc_html__( 'OpenRouter', 'woowbot-woocommerce-chatbot')?></option>
                                <option value="gemini" <?php echo (get_option( 'qcld_gemini_enabled') == 1) ? esc_attr( 'selected') :'';?> ><?php echo esc_html__( 'Gemini', 'woowbot-woocommerce-chatbot')?></option>
                                <option value="grok" <?php echo (get_option( 'qcld_grok_enabled') == 1) ? esc_attr( 'selected') :'';?> ><?php echo esc_html__( 'Grok', 'woowbot-woocommerce-chatbot')?></option>
                            </select>
                            <div class="col-auto ai-settings-title-container">
                                <button id="ai-knowledge-base-tab" class="qcld-btn-primary" link="page=chatbot_ai_setting#ai-knowledge-base-tab"><?php esc_html_e( 'Knowledge Base (RAG)', 'woowbot-woocommerce-chatbot'); ?></button>     
                            </div>
                            <div class="col-auto ai-settings-title-container">
                                <button id="wpbot-trigger-wizard" class="qcld-btn-primary"><?php esc_html_e( 'AI Wizard', 'woowbot-woocommerce-chatbot' ); ?></button>
                            </div>
                            <div class="col-auto ai-settings-title-container">
                                <button id="qcld-common-ai-settings" class="qcld-btn-primary" link="page=chatbot_ai_setting#common-ai-settings-tab"><?php esc_html_e( 'Common AI Settings', 'woowbot-woocommerce-chatbot'); ?></button>
                            </div>
                            </div>
                        </div>
    

                    </div>
                </div>
                <div id="openai-settings" class="ai-settings-provider <?php echo (get_option( 'qcld_openai_enabled') == 1 || (get_option( 'qcld_openai_enabled' ) != 1 && get_option( 'qcld_openrouter_enabled' ) != 1 && get_option( 'qcld_gemini_enabled' ) != 1 && get_option( 'qcld_grok_enabled' ) != 1)) ? 'active' : ''; ?>">
                    <?php require_once(QCLD_wpCHATBOT_PLUGIN_DIR_PATH . 'includes/integration/openai/admin/admin_ui2.php'); ?>
                </div>
                <div id="openrouter-settings" class="ai-settings-provider <?php echo (get_option( 'qcld_openrouter_enabled') == 1) ? 'active' : ''; ?>">
                    <?php require_once(QCLD_wpCHATBOT_PLUGIN_DIR_PATH . 'includes/integration/openrouter/admin/settings.php'); ?>
                </div> 
                <div id="gemini-settings" class="ai-settings-provider <?php echo (get_option( 'qcld_gemini_enabled') == 1) ? 'active' : ''; ?>">
                    <?php require_once(QCLD_wpCHATBOT_PLUGIN_DIR_PATH . 'includes/integration/gemini/admin/settings.php'); ?>
                </div>
                 <div id="grok-settings" class="ai-settings-provider <?php echo (get_option( 'qcld_grok_enabled') == 1) ? 'active' : ''; ?>">
                    <?php  require_once(QCLD_wpCHATBOT_PLUGIN_DIR_PATH . 'includes/integration/grok/admin/settings.php'); ?>
                </div>
                <div id="rag-settings" class="ai-settings-provider">
					<?php require_once QCLD_wpCHATBOT_PLUGIN_DIR_PATH . 'includes/admin/templates/rag.php'; ?>
				</div>
                <div id="common-ai-settings" class="ai-settings-provider">
                    <?php require_once QCLD_wpCHATBOT_PLUGIN_DIR_PATH . 'includes/admin/templates/common-ai-settings.php'; ?>
                </div>
                <div class="card-footer bg-dark text-white py-sm-4 border-0"></div>
            </div>



    </div>
</div>
</div>





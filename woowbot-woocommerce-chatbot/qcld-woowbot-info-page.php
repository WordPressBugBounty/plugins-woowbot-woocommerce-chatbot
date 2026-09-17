<?php
if ( ! defined( 'ABSPATH' ) ) exit;


class Qcld_woowbot_info_page
{

    function __construct()
    {
        add_action('admin_menu', array($this, 'qcopd_info_menu'));
    }

    function qcopd_info_menu(){
        
        add_submenu_page(
            'woowbot',
            esc_html__('Help', 'woowbot-woocommerce-chatbot'),
            esc_html__('Help', 'woowbot-woocommerce-chatbot'),
            'manage_options',
            'qcld_woowbot_info_page',
            array( $this, 'qcopd_info_page_content' )
        );
    }

    function qcopd_info_page_content()
    {
        ?>
        <div class="wrap qc-help-page-wrap">
            <h1><?php esc_html_e('Help', 'woowbot-woocommerce-chatbot'); ?></h1>
            <div id="post-body-content">
                <div class="qc-plugin-help-container">
                    <div class="qc-plugin-help-container-left">
                        <h3 class="qc-plugin-help-heading-lg"><?php esc_html_e('Help', 'woowbot-woocommerce-chatbot'); ?></h3>
                        <p>
                            <?php esc_html_e('Getting started with WoowBot is instantaneous. All you need to do is install and activate the plugin.', 'woowbot-woocommerce-chatbot'); ?>
                        </p>
                        <p>
                            <?php esc_html_e('You can upload your own ChatBot icon from WoowBot panel->Icons section.', 'woowbot-woocommerce-chatbot'); ?>
                        </p>
                        <p>
                            <?php esc_html_e('You can also upload a custom Agent icon in the pro version.', 'woowbot-woocommerce-chatbot'); ?>
                        </p>
                        <p>
                            <?php esc_html_e('In the lite version there are a few language settings that you can customize to your need. The default languages are fine for stores using the English language. But you can change the bot responses literally into any language!', 'woowbot-woocommerce-chatbot'); ?>
                        </p>
                        <p><?php esc_html_e('Use the custom CSS panel if you need to tweak some colors or font settings inside WoowBot.', 'woowbot-woocommerce-chatbot'); ?></p>
                        
                        <div class="clear"></div>
                        <h3 class="qc-plugin-help-heading-lg"><?php esc_html_e('Get the #1 ChatBot for WooCommerce – WoowBot', 'woowbot-woocommerce-chatbot'); ?></h3>
                        <p><strong><?php esc_html_e('Get Advanced AI Features, Customer Retargeting and more with WoowBot Pro', 'woowbot-woocommerce-chatbot'); ?></strong></p>
                        <p><?php esc_html_e('More Sales, Conversions and Satisfied customers! WoowBot is the most powerful, flexible and WooCommerce Integrated native Plug n’ Play ChatBot that can', 'woowbot-woocommerce-chatbot'); ?> <strong><?php esc_html_e('improve your sales', 'woowbot-woocommerce-chatbot'); ?></strong> <?php esc_html_e('and provide automated', 'woowbot-woocommerce-chatbot'); ?> <strong><?php esc_html_e('customer support.', 'woowbot-woocommerce-chatbot'); ?></strong></p>
                        <p><?php esc_html_e('Utilize the AI powered ChatBot services on your WooCommerce websites with', 'woowbot-woocommerce-chatbot'); ?> <strong><?php esc_html_e('Live Human Chat,', 'woowbot-woocommerce-chatbot'); ?></strong> <strong><?php esc_html_e('DialogFlow,', 'woowbot-woocommerce-chatbot'); ?></strong> <?php esc_html_e('or', 'woowbot-woocommerce-chatbot'); ?> <strong><?php esc_html_e('OpenAI', 'woowbot-woocommerce-chatbot'); ?></strong> <?php esc_html_e('(ChatGPT) along with many built-in, powerful features.', 'woowbot-woocommerce-chatbot'); ?></p>
                        <p>
                            <a target="_blank"
                               href="<?php echo esc_url('https://woowbot.pro/'); ?>"
                               class="button button-primary"><?php esc_html_e('Get the WoowBot Pro Now!', 'woowbot-woocommerce-chatbot'); ?></a>
                        </p>
                    </div>
                    <div class="qc-plugin-help-container-right">
                        <img src="<?php echo esc_url(QCLD_WOOCHATBOT_PLUGIN_URL.'images/chatbot-for-woocommerce-woowbot.png'); ?>" alt="<?php esc_attr_e('WoowBot Chatbot', 'woowbot-woocommerce-chatbot'); ?>" />
                    </div>
                </div>
                <div class="qc-plugin-help-footer">
                    <?php esc_html_e('Crafted By:', 'woowbot-woocommerce-chatbot'); ?> <a href="<?php echo esc_url('https://www.quantumcloud.net'); ?>" target="_blank"><?php esc_html_e('Web Design Company', 'woowbot-woocommerce-chatbot'); ?></a> -
                    <?php esc_html_e('QuantumCloud', 'woowbot-woocommerce-chatbot'); ?>
                </div>
            </div>
            <!-- /post-body-content -->  
        </div>
        <!-- /wrap -->
 
        <?php
    }
}

new Qcld_woowbot_info_page;
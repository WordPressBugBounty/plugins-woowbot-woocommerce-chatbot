<?php


class Qcld_woowbot_info_page
{

    function __construct()
    {
        add_action('admin_menu', array($this, 'qcopd_info_menu'));
    }

    function qcopd_info_menu(){
        
        add_submenu_page(
            'woowbot',
            esc_html__('Help'),
            esc_html__('Help'),
            'manage_options',
            'qcld_woowbot_info_page',
            array( $this, 'qcopd_info_page_content' )
        );
    }

    function qcopd_info_page_content()
    {
        ?>
        <style>


            .qc-plugin-help-container {
                padding: 20px;
                background-color: #fff;
				width: 100%;

				
            }

            .qc-plugin-help-heading-lg {
                border-bottom: 1px dashed #cccccc;
                margin: 0 0 10px;
                padding: 20px 0;
            }
			* {

				webkit-box-sizing: border-box;
				-moz-box-sizing: border-box;
				box-sizing: border-box;


			}

            
            .qc-plugin-help-container {
    display: flex;
    align-items: center;
}         
.wraphelpouter{
    position: relative;
  }
  .wraphelpouter {
    margin: 20px 0 0 0;
}

.qc-plugin-help-heading-lg {
    color: #fff;
}
.qc-plugin-help-container-right p {
    font-size: 15px;
}
div#promotion-wpchatbots {
    max-width: inherit;
    margin-left: 0;
    border: none;
}
body .qc-review-notice{
    padding: 15px 15px 15px 0 !important;
    background-color: #ffffff !important;
    border-radius: 5px !important;
    margin: 20px 20px 0 0 !important;
    border-left: 4px solid transparent !important;
    box-shadow: 0px 4px 6px 1px #ebebeb !important;
    margin-bottom: 12px !important;
}
body .qc-review-text h3 {
    color: #000000 !important;
}
body .qc-review-text p {
    color: #000000 !important;
}
body .qc-review-notice {
    z-index: 9999;
    position: relative;
}


body.lp-submenu-qcld_woowbot_info_page{

}

body.lp-submenu-qcld_woowbot_info_page{
        background-color: #F5F7FD;
        
}

.qc-plugin-help-container {
    width: 100%;
    padding: 20px 20px;
    background: #ffffff;
    border-radius: 16px;
    margin: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.qc-plugin-help-container-right a.button.button-primary {
    padding: 10px 20px;
    width: auto;
    font-size: 16px;
    font-weight: 500;
    color: #988FBD;
    border-radius: 8px;
    position: relative;
    margin: 0 0 5px 0;
    background: #5B4E96 !important;
    color: #ffffff !important;
    border: none;
}
@media only screen and (max-width: 1024px) {

.qc-plugin-help-container {
display: flex;
    justify-content: space-between;
    align-items: center;
    flex-direction: column;
}
}
        </style>

<div class="wraphelpouter">





        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder">
            
                    <div id="post-body-content" style="position: relative;">
                        <div class="qc-plugin-help-container">
                        <div class="qc-plugin-help-container-right">
                            <h3 class="qc-plugin-help-heading-lg" style="color:#000;"><?php esc_html_e('Help', 'woowbot-woocommerce-chatbot'); ?></h3>
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
                            <h3 class="qc-plugin-help-heading-lg" style="color:#000;"><?php esc_html_e('Get the #1 ChatBot for WooCommerce – WoowBot', 'woowbot-woocommerce-chatbot'); ?></h3>
                            <p style="font-weight:bold;"><?php esc_html_e('Get Advanced AI Features, Customer Retargeting and more with WoowBot Pro', 'woowbot-woocommerce-chatbot'); ?></p>
                            <p>More Sales, Conversions and Satisfied customers! WoowBot is the most powerful, flexible and WooCommerce Integrated native Plug n’ Play ChatBot that can <span style="font-weight:bold;">improve your sales</span> and and provide automated <span style="font-weight:bold;">customer support.</span></p>
                            <p>Utilize the AI powered ChatBot services on your WooCommerce websites with <span style="font-weight:bold;">Live Human Chat,</span> <span style="font-weight:bold;">DialogFlow,</span> or <span style="font-weight:bold;">OpenAI</span> (ChatGPT) along with many built-in, powerful features.</p>
                            <p style="text-align: left">
                                <a target="_blank"
                                   href="<?php echo esc_url('https://woowbot.pro/'); ?>"
                                   class="button button-primary"><?php esc_html_e('Get the WoowBot Pro Now!', 'woowbot-woocommerce-chatbot'); ?></a>

                            </p>
                            </div>
                            <div class="qc-plugin-help-container-right">
                        <img src="<?php echo esc_url(QCLD_WOOCHATBOT_PLUGIN_URL.'images/chatbot-for-woocommerce-woowbot.png'); ?>" />
                        </div>
                        </div>
                        <div style="padding: 15px 10px; text-align: center; margin-top: 20px;margin-left: 0;color: #000;background: #fff;width:100%;     border-radius: 16px;">
                            <?php esc_html_e('Crafted By:', 'woowbot-woocommerce-chatbot'); ?> <a href="<?php echo esc_url('http://www.quantumcloud.com'); ?>" target="_blank"><?php esc_html_e('Web Design Company', 'woowbot-woocommerce-chatbot'); ?></a> -
                            <?php echo esc_attr('QuantumCloud', 'woowbot-woocommerce-chatbot'); ?>
                        </div>

                    </div>
                    <!-- /post-body-content -->


                </div>
                <!-- /post-body-->

            </div>
            <!-- /poststuff -->

        </div>
        <!-- /wrap -->
        </div>
        <?php
    }
}

new Qcld_woowbot_info_page;
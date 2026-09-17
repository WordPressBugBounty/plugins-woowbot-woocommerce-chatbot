<?php
defined('ABSPATH') or die("You can't access this file directly.");
/*
* QuantumCloud Promo + Support Page
* Revised On: 18-10-2023
*/

if ( ! defined( 'qcld_wpbot_free_support_path' ) ) {
    define('qcld_wpbot_free_support_path', plugin_dir_path(__FILE__));
}

if ( ! defined( 'qcld_wpbot_free_support_url' ) )
    define('qcld_wpbot_free_support_url', plugin_dir_url( __FILE__ ) );

if ( ! defined( 'qcld_wpbot_free_img_url' ) )
    define('qcld_wpbot_free_img_url', qcld_wpbot_free_support_url . "/images" );


/*Callback function to add the menu */
function qcld_wpbot_free_show_promo_page_callback_func(){

    add_submenu_page(
        "woowbot",
        esc_html__('More WordPress Goodies for You!', 'woowbot-woocommerce-chatbot'),
        esc_html__('Support', 'woowbot-woocommerce-chatbot'),
        'manage_options',
        "qcpro-promo-page-woowbot-support",
        'qcld_wpbot_free_promo_support_page_callback_func'
    );
    
} //show_promo_page_callback_func

add_action( 'admin_menu', 'qcld_wpbot_free_show_promo_page_callback_func', 10 );


/*******************************
 * Main Class to Display Support
 * form and the promo pages
 *******************************/

if ( ! function_exists( 'qcld_wpbot_free_include_promo_page_scripts' ) ) {	
	function qcld_wpbot_free_include_promo_page_scripts( ) {   


        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

        if ( 'qcpro-promo-page-woowbot-support' === $page ){

                                         
            wp_enqueue_style( 'qcld-support-style-css', qcld_wpbot_free_support_url . "css/style.css", array(), QCLD_WOOCHATBOT_VERSION );

            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'jquery-ui-core');
            wp_enqueue_script( 'jquery-ui-tabs' );
            wp_enqueue_script( 'jquery-woowbot-form-processor', qcld_wpbot_free_support_url . 'js/support-form-script.js',  array('jquery', 'jquery-ui-core','jquery-ui-tabs'), QCLD_WOOCHATBOT_VERSION, true );

            wp_add_inline_script( 'jquery-woowbot-form-processor', 
                                    'var qcld_wpbot_free_ajaxurl    = "' . admin_url('admin-ajax.php') . '";
                                    var qcld_wpbot_free_ajax_nonce  = "'. wp_create_nonce( 'woowbot-woocommerce-chatbot' ).'";   
                                ', 'before');
            
        }
	   
	}
	add_action('admin_enqueue_scripts', 'qcld_wpbot_free_include_promo_page_scripts');
	
}
		
/*******************************
 * Callback function to show the HTML
 *******************************/

include_once qcld_wpbot_free_support_path . '/qc-clr-recommendbot-support-plugin.php';

if ( ! function_exists( 'qcld_wpbot_free_promo_support_page_callback_func' ) ) {

	function qcld_wpbot_free_promo_support_page_callback_func() {
		
?>


        <div class="wrap">
            <h2><?php esc_html_e('Support', 'woowbot-woocommerce-chatbot'); ?></h2>
        <div class="qc-woowbot-support qcld-support-new-page">
            <div class="support-btn-main justify-content-center">
                <div class="col text-center">
                    <h2 class="py-3"><?php esc_html_e('Check Out Some of Our Other Works that Might Make Your Website Better', 'woowbot-woocommerce-chatbot'); ?></h2>
                    <h5><?php esc_html_e('All our Pro Version users get Premium, Guaranteed Quick, One on One Priority Support.', 'woowbot-woocommerce-chatbot'); ?></h5>
                    <div class="support-btn">
                        <a class="premium-support" href="<?php echo esc_url('https://qc.turbopowers.com/'); ?>" target="_blank"><?php esc_html_e('Get Priority Support ', 'woowbot-woocommerce-chatbot'); ?></a>
                        <a class="premium-support premium-support-kb" href="<?php echo esc_url('https://www.quantumcloud.net/resources/kb-sections/woowbot-chatbot/'); ?>" target="_blank"><?php esc_html_e('Online KnowledgeBase', 'woowbot-woocommerce-chatbot'); ?></a>
                    </div>
                </div>
            
                <div class="qc-column-12" >
                    <div class="support-btn">
                        
                        <a class="premium-support premium-support-free" href="<?php echo esc_url('https://wordpress.org/support/plugin/woowbot-woocommerce-chatbot/') ?>" target="_blank"><?php esc_html_e('Get Support for Free Version', 'woowbot-woocommerce-chatbot') ?></a>
                    </div>
                </div>
            </div>
            
            <div class="qcld-plugins-lists">
                <div class="qcld-plugins-loading">
                    <img src="<?php echo esc_url(qcld_wpbot_free_img_url); ?>/loading.gif" alt="loading">
                </div>
            </div>
        </div>
        </div>
			

    

<?php
            
       
    }
}


/*******************************
 * Handle Ajex Request for Form Processing
 *******************************/
add_action( 'wp_ajax_qcld_wpbot_free_process_qc_promo_form', 'qcld_wpbot_free_process_qc_promo_form' );

if( !function_exists('qcld_wpbot_free_process_qc_promo_form') ){

    function qcld_wpbot_free_process_qc_promo_form(){

        check_ajax_referer( 'woowbot-woocommerce-chatbot', 'security');
        
        $data['status']   = 'failed';
        $data['message']  = wp_kses_post( __( 'Problem in processing your form submission request! Apologies for the inconveniences.<br> Please email to <span class="qcld-support-email-highlight"> quantumcloud@gmail.com </span> with any feedback. We will get back to you right away!', 'woowbot-woocommerce-chatbot' ) );

        $name         = isset($_POST['post_name']) ? trim(sanitize_text_field(wp_unslash($_POST['post_name']))) : '';
        $email        = isset($_POST['post_email']) ? trim(sanitize_email(wp_unslash($_POST['post_email']))) : '';
        $subject      = isset($_POST['post_subject']) ? trim(sanitize_text_field(wp_unslash($_POST['post_subject']))) : '';
        $message      = isset($_POST['post_message']) ? trim(sanitize_text_field(wp_unslash($_POST['post_message']))) : '';
        $plugin_name  = isset($_POST['post_plugin_name']) ? trim(sanitize_text_field(wp_unslash($_POST['post_plugin_name']))) : '';

        if( $name == "" || $email == "" || $subject == "" || $message == "" )
        {
            $data['message'] = esc_html__('Please fill up all the requried form fields.', 'woowbot-woocommerce-chatbot');
        }
        else if ( filter_var($email, FILTER_VALIDATE_EMAIL) === false ) 
        {
            $data['message'] = esc_html__('Invalid email address.', 'woowbot-woocommerce-chatbot');
        }
        else
        {

            //build email body

            $bodyContent = "";
                
            $bodyContent .= "<p><strong>".esc_html__('Support Request Details:', 'woowbot-woocommerce-chatbot')."</strong></p><hr>";

            $bodyContent .= "<p>".esc_html__('Name', 'woowbot-woocommerce-chatbot')." : ".esc_html($name)."</p>";
            $bodyContent .= "<p>".esc_html__('Email', 'woowbot-woocommerce-chatbot')." : ".esc_html($email)."</p>";
            $bodyContent .= "<p>".esc_html__('Subject', 'woowbot-woocommerce-chatbot')." : ".esc_html($subject)."</p>";
            $bodyContent .= "<p>".esc_html__('Message', 'woowbot-woocommerce-chatbot')." : ".esc_html($message)."</p>";

            $bodyContent .= "<p>".esc_html__('Sent Via the Plugin', 'woowbot-woocommerce-chatbot')." : ".esc_html($plugin_name)."</p>";

            $bodyContent .="<p></p><p>".esc_html__('Mail sent from:', 'woowbot-woocommerce-chatbot')." <strong>".esc_html(get_bloginfo('name'))."</strong>, ".esc_html__('URL:', 'woowbot-woocommerce-chatbot')." [".esc_url(get_bloginfo('url'))."].</p>";
            $bodyContent .="<p>".esc_html__('Mail Generated on:', 'woowbot-woocommerce-chatbot')." " . gmdate("F j, Y, g:i a") . "</p>";           
            
            $toEmail = "quantumcloud@gmail.com"; //Receivers email address
            //$toEmail = "qc.kadir@gmail.com"; //Receivers email address

            //Extract Domain
            $url = get_site_url();
            $url = wp_parse_url($url);
            $domain = isset($url['host']) ? $url['host'] : '';
            

            $fakeFromEmailAddress = "wordpress@" . $domain;
            
            $to = $toEmail;
            $body = $bodyContent;
            $headers = array();
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $headers[] = 'From: '.esc_attr($name).' <'.esc_attr($fakeFromEmailAddress).'>';
            $headers[] = 'Reply-To: '.esc_attr($name).' <'.esc_attr($email).'>';

            $finalSubject = esc_html__('From Plugin Support Page:', 'woowbot-woocommerce-chatbot')." " . esc_attr($subject);
            
            $result = wp_mail( $to, $finalSubject, $body, $headers );

            if( $result )
            {
                $data['status'] = 'success';
                $data['message'] = esc_html__('Your email was sent successfully. Thanks!', 'woowbot-woocommerce-chatbot');
            }

        }

        ob_clean();

        wp_send_json($data);
    }
}
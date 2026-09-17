<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( 'Wp_Usage_Feedback') ) {
	
	class Wp_Usage_Feedback {
		
		private $wpbot_version = '1.0.0';
		private $home_url = '';
		private $plugin_file = '';
		private $plugin_name = '';
		private $options = array();
		private $require_optin = true;
		private $include_goodbye_form = true;

		
		/**
		 * Class constructor
		 *
		 * @param $_home_url				The URL to the site we're sending data to
		 * @param $_plugin_file				The file path for this plugin
		 * @param $_options					Plugin options to track
		 * @param $_require_optin			Whether user opt-in is required (always required on WordPress.org)
		 * @param $_include_goodbye_form	Whether to include a form when the user deactivates
		 * @param $_marketing				Marketing method:
		 *									0: Don't collect email addresses
		 *									1: Request permission same time as tracking opt-in
		 *									2: Request permission after opt-in
		 */
		public function __construct( 
			$_plugin_file,
			$_home_url,
			
			$_require_optin=true,
			$_include_goodbye_form=true) {

			$this->plugin_file = $_plugin_file;
			$this->home_url = 'plugins@quantumcloud.net';
			$this->plugin_name = basename( $this->plugin_file, '.php' );

			$this->require_optin = $_require_optin;
			$this->include_goodbye_form = $_include_goodbye_form;


			// Deactivation hook
			register_deactivation_hook( $this->plugin_file, array( $this, 'deactivate_this_plugin' ) );
			
			// Get it going
			$this->init();
			
		}
		
		public function init() {
			
			// Deactivation
			add_filter( 'plugin_action_links_' . plugin_basename( $this->plugin_file ), array( $this, 'filter_action_links' ) );
			add_action( 'admin_footer-plugins.php', array( $this, 'goodbye_ajax' ) );
			add_action( 'wp_ajax_goodbye_form', array( $this, 'goodbye_form_callback' ) );
			
			
		}

		// In theme's functions.php or plug-in code:

		function set_content_type(){
			return "text/html";
		}
		
		
		/**
		 * Send the data to the home site
		 *
		 * @since 1.0.0
		 */
		public function send_data( $body ) {
			$message = '';
			foreach($body as $key=>$value){
				
				if($key=='active_plugins'){
					$message .='<p> <b>'.$key.'</b>: '.(implode(', ',$value)).' </p>';
				}
				elseif($key=='inactive_plugins'){
					$message .='<p> <b>'.$key.'</b>: '.(implode(', ',$value)).' </p>';
				}else{
					$message .='<p> <b>'.$key.'</b>: '.$value.' </p>';
				}
				
			}
			
			    $title   = 'Plugin Deactivation Notice';
				$headers = array('From: Anonymous <mailer@just-a-fake-from-address.com>');
				
				add_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
				$email = wp_mail($this->home_url, $title, $message, $headers);
				remove_filter('wp_mail_content_type', array($this, 'set_content_type'));

				return $email;

		}
		
		/**
		 * Here we collect most of the data
		 * 
		 * @since 1.0.0
		 */
		public function get_data() {
	
			// Use this to pass error messages back if necessary
			$body['message'] = '';
	
			// Use this array to send data back
			$body = array();


	
			/**
			 * Get our plugin data
			 * Currently we grab plugin name and version
			 * Or, return a message if the plugin data is not available
			 * @since 1.0.0
			 */
			$plugin = $this->plugin_data();
			if( empty( $plugin ) ) {
				// We can't find the plugin data
				// Send a message back to our home site
				$body['message'] .= __( 'We can\'t detect any plugin information. This is most probably because you have not included the code in the plugin main file.', 'woowbot-woocommerce-chatbot' );
				$body['status'] = 'Data not found'; // Never translated
			} else {
				if( isset( $plugin['Name'] ) ) {
					$body['plugin'] = sanitize_text_field( $plugin['Name'] );
				}
				if( isset( $plugin['Version'] ) ) {
					$body['version'] = sanitize_text_field( $plugin['Version'] );
				}

			}

			// Return the data
			return $body;
	
		}
		
		/**
		 * Return plugin data
		 * @since 1.0.0
		 */
		public function plugin_data() {
			// Being cautious here
			if( ! function_exists( 'get_plugin_data' ) ) {
				include ABSPATH . '/wp-admin/includes/plugin.php';
			}
			// Retrieve current plugin information
			$plugin = get_plugin_data( $this->plugin_file );
			return $plugin;
		}

		/**
		 * Deactivating plugin
		 * @since 1.0.0
		 */
		public function deactivate_this_plugin() {

			$body = $this->get_data();
			$body['status'] = 'Deactivated'; // Never translated
			$body['deactivated_date'] = gmdate('Y-m-d');
			
			// Add deactivation form data
			if( false !== get_option( 'wpbot_deactivation_reason_' . $this->plugin_name ) ) {
				$body['deactivation_reason'] = get_option( 'wpbot_deactivation_reason_' . $this->plugin_name );
				delete_option('wpbot_deactivation_reason_' . $this->plugin_name);
				delete_option('wp_chatbot_show_posts');
				delete_option('wp_chatbot_show_pages');
				delete_option('wp_chatbot_show_pages_list');
				delete_option('wp_chatbot_exclude_post_list');
				delete_option('wp_chatbot_show_wpcommerce');
			}
			if( false !== get_option( 'wpbot_deactivation_details_' . $this->plugin_name ) ) {
				$body['deactivation_details'] = get_option( 'wpbot_deactivation_details_' . $this->plugin_name );
				delete_option('wpbot_deactivation_details_' . $this->plugin_name);
				delete_option('wp_chatbot_show_posts');
				delete_option('wp_chatbot_show_pages');
				delete_option('wp_chatbot_show_pages_list');
				delete_option('wp_chatbot_exclude_post_list');
				delete_option('wp_chatbot_show_wpcommerce');
			}
			
			if(isset($body['deactivation_reason']) or isset($body['deactivation_details']))
				$this->send_data( $body );
			

		}
		
		/**
		 * Filter the deactivation link to allow us to present a form when the user deactivates the plugin
		 * @since 1.0.0
		 */
		public function filter_action_links( $links ) {

			if( isset( $links['deactivate'] ) && $this->include_goodbye_form ) {
				$deactivation_link = $links['deactivate'];
				// Insert an onClick action to allow form before deactivating
				$deactivation_link = str_replace( '<a ', '<div class="wpb-goodbye-form-wrapper"><span class="wpb-goodbye-form" id="wpb-goodbye-form-' . esc_attr( $this->plugin_name ) . '"></span></div><a onclick="javascript:event.preventDefault();" id="wpb-goodbye-link-' . esc_attr( $this->plugin_name ) . '" ', $deactivation_link );
				$links['deactivate'] = $deactivation_link;
			}
			return $links;
		}
		
		/*
		 * Form text strings
		 * These are non-filterable and used as fallback in case filtered strings aren't set correctly
		 * @since 1.0.0
		 */
		public function form_default_text() {
			$form = array();
			$form['heading'] = __( 'Sorry to see you go', 'woowbot-woocommerce-chatbot' );
			$form['body'] = '';
			$form['options'] = array(
				__( 'Found a Bug', 'woowbot-woocommerce-chatbot' ),
				__( 'Need More Features', 'woowbot-woocommerce-chatbot' ),
				__( 'Deactivating Temporarily', 'woowbot-woocommerce-chatbot' ),
				__( 'Upgrading to Pro', 'woowbot-woocommerce-chatbot' ),

			);
			$form['email'] = __( 'Please provide email so we can contact with bug fixes', 'woowbot-woocommerce-chatbot' );
			$form['details'] = __( 'Please provide some details so we can improve the plugin', 'woowbot-woocommerce-chatbot' );
			return $form;
		}
		
		/**
		 * Form text strings
		 * These can be filtered
		 * The filter hook must be unique to the plugin
		 * @since 1.0.0
		 */
		public function form_filterable_text() {
			$form = $this->form_default_text();
			return apply_filters( 'wpbot_form_text_' . esc_attr( $this->plugin_name ), $form );
		}
		
		/**
		 * Form text strings
		 * These can be filtered
		 * @since 1.0.0
		 */
		public function goodbye_ajax() {
			// Get our strings for the form
			$form = $this->form_filterable_text();
			if( ! isset( $form['heading'] ) || ! isset( $form['body'] ) || ! isset( $form['options'] ) || ! is_array( $form['options'] ) || ! isset( $form['details'] ) ) {
				// If the form hasn't been filtered correctly, we revert to the default form
				$form = $this->form_default_text();
			}
			// Build the HTML to go in the form
			$html = '<div class="wpb-goodbye-form-head"><strong>' . esc_html( $form['heading'] ) . '</strong><span class="wpb-goodbye-form-close" title="' . esc_attr__( 'Close', 'woowbot-woocommerce-chatbot' ) . '">&times;</span></div>';
			$html .= '<div class="wpb-goodbye-form-body">';
			if ( ! empty( $form['body'] ) ) {
				$html .= '<p class="wpb-goodbye-form-desc">' . esc_html( $form['body'] ) . '</p>';
			}
			if( is_array( $form['options'] ) ) {
				$html .= '<div class="wpb-goodbye-options"><div id="wpb_additional_content">';
				$html .= '<div class="wpb-form-group"><label for="wpb-goodbye-email">' . esc_html( $form['email'] ) . ' <span class="wpb-optional">(' . esc_html__( 'Optional', 'woowbot-woocommerce-chatbot' ) . ')</span></label>';
				$html .= '<input type="email" name="wpb-goodbye-email" id="wpb-goodbye-email" placeholder="' . esc_attr__( 'Enter your email', 'woowbot-woocommerce-chatbot' ) . '" value="' . esc_attr( get_option('admin_email') ) . '" /></div>';
				
				$html .= '<div class="wpb-form-group"><label for="wpb-goodbye-reasons">' . esc_html( $form['details'] ) . ' <span class="wpb-required">*</span></label>';
				$html .= '<textarea name="wpb-goodbye-reasons" id="wpb-goodbye-reasons" rows="3" placeholder="' . esc_attr__( 'Please share some details so we can improve the plugin...', 'woowbot-woocommerce-chatbot' ) . '"></textarea><div id="wpbot_deactivation_error"></div></div>';
				$html .= '</div><!-- #wpb_additional_content --></div><!-- .wpb-goodbye-options -->';
			}
			$html .= '</div><!-- .wpb-goodbye-form-body -->';
			$html .= '<div class="deactivating-spinner"><span class="spinner is-active"></span> ' . esc_html__( 'Submitting feedback...', 'woowbot-woocommerce-chatbot' ) . '</div>';
			?>
			<style type="text/css">
				.wpb-form-active .wpb-goodbye-form-bg {
					background: rgba(15, 23, 42, 0.55);
					backdrop-filter: blur(4px);
					-webkit-backdrop-filter: blur(4px);
					position: fixed;
					top: 0;
					left: 0;
					width: 100%;
					height: 100%;
					z-index: 99998;
					transition: all 0.3s ease;
				}
				.wpb-goodbye-form-wrapper {
					position: relative;
					z-index: 99999;
					display: none;
				}
				.wpb-form-active .wpb-goodbye-form-wrapper {
					display: block;
				}
				.wpb-goodbye-form {
					display: none;
				}
				.wpb-form-active .wpb-goodbye-form {
					position: fixed;
					max-width: 480px;
					width: 92%;
					background: #ffffff;
					white-space: normal;
					z-index: 99999;
					top: 50%;
					left: 50%;
					transform: translate(-50%, -50%);
					border-radius: 12px;
					box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(0, 0, 0, 0.05);
					overflow: hidden;
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
					box-sizing: border-box;
					animation: wpbModalFadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
				}
				@keyframes wpbModalFadeIn {
					from {
						opacity: 0;
						transform: translate(-50%, -46%) scale(0.96);
					}
					to {
						opacity: 1;
						transform: translate(-50%, -50%) scale(1);
					}
				}
				.wpb-goodbye-form * {
					box-sizing: border-box;
				}
				.wpb-goodbye-form-head {
					background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%);
					color: #ffffff;
					padding: 16px 20px;
					display: flex;
					align-items: center;
					justify-content: space-between;
				}
				.wpb-goodbye-form-head strong {
					font-size: 16px;
					font-weight: 600;
					letter-spacing: -0.01em;
					color: #ffffff;
				}
				.wpb-goodbye-form-close {
					cursor: pointer;
					font-size: 20px;
					line-height: 1;
					color: rgba(255, 255, 255, 0.75);
					transition: color 0.15s ease, transform 0.15s ease;
					border-radius: 4px;
					padding: 2px 6px;
					user-select: none;
				}
				.wpb-goodbye-form-close:hover {
					color: #ffffff;
					background: rgba(255, 255, 255, 0.15);
				}
				.wpb-goodbye-form-body {
					padding: 20px 22px 14px 22px;
					color: #334155;
				}
				.wpb-goodbye-form-desc {
					margin: 0 0 14px 0;
					font-size: 13.5px;
					color: #64748b;
					line-height: 1.5;
				}
				.wpb-form-group {
					margin-bottom: 15px;
				}
				.wpb-form-group label {
					display: block;
					font-size: 13px;
					font-weight: 600;
					color: #1e293b;
					margin-bottom: 6px;
				}
				.wpb-form-group .wpb-optional {
					font-weight: 400;
					font-size: 12px;
					color: #94a3b8;
					margin-left: 4px;
				}
				.wpb-form-group .wpb-required {
					color: #ef4444;
					margin-left: 2px;
				}
				.wpb-goodbye-form-body input[type="email"],
				.wpb-goodbye-form-body textarea {
					width: 100%;
					padding: 9px 12px;
					border: 1px solid #cbd5e1;
					border-radius: 8px;
					font-size: 13.5px;
					color: #0f172a;
					background: #f8fafc;
					transition: all 0.2s ease;
					outline: none;
					box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.03);
					font-family: inherit;
				}
				.wpb-goodbye-form-body input[type="email"]:focus,
				.wpb-goodbye-form-body textarea:focus {
					background: #ffffff;
					border-color: #7c3aed;
					box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
				}
				.wpb-goodbye-form-body textarea {
					min-height: 85px;
					resize: vertical;
				}
				#wpbot_deactivation_error {
					color: #ef4444;
					font-size: 12px;
					margin-top: 6px;
					font-weight: 500;
					display: none;
				}
				.deactivating-spinner {
					display: none;
					padding: 30px 20px;
					text-align: center;
					color: #475569;
					font-size: 14px;
					font-weight: 500;
				}
				.deactivating-spinner .spinner {
					float: none;
					margin: 0 8px 0 0;
					vertical-align: middle;
					visibility: visible;
				}
				.wpb-goodbye-form-footer {
					padding: 14px 22px;
					background: #f8fafc;
					border-top: 1px solid #f1f5f9;
					display: flex;
					align-items: center;
					justify-content: space-between;
				}
				.wpbot_just_deactivate {
					color: #64748b !important;
					font-size: 13px !important;
					text-decoration: none !important;
					font-weight: 500 !important;
					padding: 7px 12px !important;
					border-radius: 6px !important;
					transition: all 0.15s ease !important;
				}
				.wpbot_just_deactivate:hover {
					color: #0f172a !important;
					background: #e2e8f0 !important;
				}
				.wpbot_submit_deactivate {
					background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%) !important;
					color: #ffffff !important;
					border: none !important;
					padding: 8px 18px !important;
					border-radius: 6px !important;
					font-size: 13px !important;
					font-weight: 600 !important;
					cursor: pointer !important;
					text-decoration: none !important;
					box-shadow: 0 2px 4px rgba(124, 58, 237, 0.25) !important;
					transition: all 0.2s ease !important;
					display: inline-block !important;
				}
				.wpbot_submit_deactivate:hover,
				.wpbot_submit_deactivate:focus {
					background: linear-gradient(135deg, #6d28d9 0%, #4f46e5 100%) !important;
					color: #ffffff !important;
					box-shadow: 0 4px 6px rgba(124, 58, 237, 0.35) !important;
					transform: translateY(-1px);
				}
			</style>
			<div class="wpb-goodbye-form-bg"></div>
			<script>
				jQuery(document).ready(function($){
					$("#wpb-goodbye-link-<?php echo esc_attr( $this->plugin_name ); ?>").on("click",function(e){
						e.preventDefault();
						var url = $(this).attr('href');
						$('body').addClass('wpb-form-active');
						var $form = $("#wpb-goodbye-form-<?php echo esc_attr( $this->plugin_name ); ?>");
						$form.fadeIn(200);
						$form.html( '<?php /* phpcs:ignore */ echo $html; ?>' + '<div class="wpb-goodbye-form-footer"><a class="wpbot_just_deactivate" href="'+url+'"><?php echo esc_js( __( 'Skip & Deactivate', 'woowbot-woocommerce-chatbot' ) ); ?></a> <a id="wpb-submit-form" class="button primary wpbot_submit_deactivate" href="#"><?php echo esc_js( __( 'Submit & Deactivate', 'woowbot-woocommerce-chatbot' ) ); ?></a></div>');
						$('#wpb-goodbye-reasons').focus();

						$('.wpb-goodbye-form-close, .wpb-goodbye-form-bg').on('click', function(){
							$form.fadeOut(200);
							$('body').removeClass('wpb-form-active');
						});

						$('#wpb-submit-form').on('click', function(ev){
							ev.preventDefault();
							if($.trim($('#wpb-goodbye-reasons').val()) == ''){
								$('#wpbot_deactivation_error').html('<?php echo esc_js( __( 'Please provide some details to improve the plugin for you!', 'woowbot-woocommerce-chatbot' ) ); ?>').show();
								$('#wpb-goodbye-reasons').focus();
								return;
							}
							$('#wpbot_deactivation_error').hide();

							// As soon as we click, the body and footer of the form should disappear
							$form.find(".wpb-goodbye-form-body, .wpb-goodbye-form-footer").fadeOut(150);
							// Fade in spinner
							$form.find(".deactivating-spinner").fadeIn(150);

							var email = $('#wpb-goodbye-email').val();
							var details = $('#wpb-goodbye-reasons').val();
							var data = {
								'action': 'goodbye_form',
								'details': details,
								'email': email,
								'security': "<?php echo sanitize_key( wp_create_nonce ( 'wpbot_goodbye_form' ) ); ?>",
								'dataType': "json"
							};

							$.post(
								ajaxurl,
								data,
								function(response){
									window.location.href = url;
								}
							).fail(function(){
								window.location.href = url;
							});
						});
					});
				});
			</script>
		<?php }
		
		/**
		 * AJAX callback when the form is submitted
		 * @since 1.0.0
		 */
		public function goodbye_form_callback() {
			check_ajax_referer( 'wpbot_goodbye_form', 'security' );

			if( isset( $_POST['details'] ) ) {
				$details = sanitize_text_field( wp_unslash( $_POST['details'] ) );
				update_option( 'wpbot_deactivation_details_' . $this->plugin_name, $details );
			}

			echo 'success';
			wp_die();
		}
		
	}
	
}



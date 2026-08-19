<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}


// Declare Constants
if ( ! defined( 'ai_tryon_for_wc_version' ) ) {
  define('ai_tryon_for_wc_version', '1.0.0');
}
if ( ! defined( 'ai_tryon_for_wc_url' ) ) {
  define('ai_tryon_for_wc_url', plugin_dir_url(__FILE__));
}
if ( ! defined( 'ai_tryon_for_wc_img_url' ) ) {
  define('ai_tryon_for_wc_img_url', ai_tryon_for_wc_url . "/assets/images");
}
if ( ! defined( 'ai_tryon_for_wc_asset_url' ) ) {
  define('ai_tryon_for_wc_asset_url', ai_tryon_for_wc_url . "/assets");
}
if ( ! defined( 'ai_tryon_for_wc_dir' ) ) {
  define('ai_tryon_for_wc_dir', dirname(__FILE__));
}

// Check for WooCommerce dependency
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
/*if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
  add_action( 'admin_notices', 'qcld_woo_chatbot_woocommerce_missing_notice' );
  return;
}*/

// Includes Files
require_once( 'inc/ai-tryon-for-woocommerce-assets.php' );
require_once( 'inc/ai-tryon-for-woocommerce-ajax.php' );
require_once( 'inc/ai-tryon-for-woocommerce-admin.php' );
require_once( 'inc/ai-tryon-for-woocommerce-functions.php' );

function qcld_woo_chatbot_woocommerce_missing_notice() {
  ?>
  <div class="notice notice-error">
    <p><?php echo wp_kses_post( esc_html( '<strong>AI Try-On Addon for WooCommerce</strong> requires WooCommerce to be installed and active. The plugin\'s features will not function until WooCommerce is active.', 'ai-tryon-for-woocommerce' ) ); ?></p>
  </div>
  <?php
}



/*if ( ! function_exists( 'qcld_woo_chatbot_activation_redirect' ) ) {
  function qcld_woo_chatbot_activation_redirect( $plugin ) {

    $screen = get_current_screen();

    if( ( isset( $screen->base ) && $screen->base == 'plugins' ) && $plugin == plugin_basename( __FILE__ ) ) {

      exit( esc_url( wp_safe_redirect( admin_url( 'admin.php?page=ai-tryon-for-woocommerce#general') ) ) );
    }

  }
}
add_action( 'activated_plugin', 'qcld_woo_chatbot_activation_redirect' );*/

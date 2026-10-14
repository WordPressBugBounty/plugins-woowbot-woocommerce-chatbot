<?php
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'QCLD_WIA_VERSION' ) ) {
	define( 'QCLD_WIA_VERSION', '0.5.0' );
}
if ( ! defined( 'QCLD_WIA_FILE' ) ) {
	define( 'QCLD_WIA_FILE', __FILE__ );
}
if ( ! defined( 'QCLD_WIA_PATH' ) ) {
	define( 'QCLD_WIA_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'QCLD_WIA_URL' ) ) {
	define( 'QCLD_WIA_URL', plugin_dir_url( __FILE__ ) );
}

require_once QCLD_WIA_PATH . 'includes/class-wia-analytics.php';
require_once QCLD_WIA_PATH . 'includes/class-wia-openai.php';
require_once QCLD_WIA_PATH . 'includes/class-wia-rest.php';
require_once QCLD_WIA_PATH . 'includes/class-wia-admin.php';

final class QCLD_WIA_Plugin {
	public static function init() {
		add_action( 'before_woocommerce_init', array( __CLASS__, 'declare_hpos_compatibility' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'boot' ) );
	}

	public static function declare_hpos_compatibility() {
		
		if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
			$plugin_file = defined( 'QCLD_WOOCHATBOT_FILE_URL' ) ? QCLD_WOOCHATBOT_FILE_URL : ( defined( 'QC_PLUGIN_DIR' ) ? QC_PLUGIN_DIR . '/qcld-wpwbot.php' : dirname( __FILE__, 5 ) . '/qcld-wpwbot.php' );

			if ( ! file_exists( $plugin_file ) ) {
				if ( defined( 'ai_tryon_for_wc_dir' ) && file_exists( ai_tryon_for_wc_dir . '/ai-tryon-for-woocommerce.php' ) ) {
					$plugin_file = ai_tryon_for_wc_dir . '/ai-tryon-for-woocommerce.php';
				} else {
					$plugin_file = QCLD_WIA_FILE;
				}
			}

			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $plugin_file, true );
		}
	}

	public static function boot() {
		QCLD_WIA_Admin::init();
		QCLD_WIA_REST::init();
	}

}

QCLD_WIA_Plugin::init();

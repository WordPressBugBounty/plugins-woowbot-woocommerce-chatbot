<?php
defined( 'ABSPATH' ) || exit;

define( 'QCLD_WIA_VERSION', '0.5.0' );
define( 'QCLD_WIA_FILE', __FILE__ );
define( 'QCLD_WIA_PATH', plugin_dir_path( __FILE__ ) );
define( 'QCLD_WIA_URL', plugin_dir_url( __FILE__ ) );

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
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', QCLD_WIA_FILE, true );
		}
	}

	public static function boot() {
		QCLD_WIA_Admin::init();
		QCLD_WIA_REST::init();
	}

}

QCLD_WIA_Plugin::init();

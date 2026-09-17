<?php
defined( 'ABSPATH' ) || exit;

final class QCLD_WIA_Admin {
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function menu() {
		add_submenu_page( 'ai-tryon-for-woocommerce', __( 'AI Business Analyst', 'woowbot-woocommerce-chatbot' ), __( 'AI Business Analyst', 'woowbot-woocommerce-chatbot' ), 'manage_options', 'ai-woo-insights', array( __CLASS__, 'page' ) );
	}

	public static function assets( $hook ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		if ( 'ai-woo-insights' !== $page && strpos( $hook, 'ai-woo-insights' ) === false ) { return; }
		wp_enqueue_style( 'qcld-wia-admin', QCLD_WIA_URL . 'assets/admin.css', array(), QCLD_WIA_VERSION );
		wp_enqueue_script( 'qcld-wia-admin', QCLD_WIA_URL . 'assets/admin.js', array( 'jquery' ), QCLD_WIA_VERSION, true );
		wp_localize_script( 'qcld-wia-admin', 'qcld_wia', array( 'endpoint' => rest_url( 'woo-insights-ai/v1/ask' ), 'historyEndpoint' => rest_url( 'woo-insights-ai/v1/history' ), 'snapshotEndpoint' => rest_url( 'woo-insights-ai/v1/snapshot' ), 'nonce' => wp_create_nonce( 'wp_rest' ) ) );
	}

	public static function page() {
		$provider = get_option( 'qcld_woo_chatbot_api_provider', 'gemini' );
		$provider_name = ( 'openai' === $provider ) ? 'OpenAI GPT' : 'Google Gemini';
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'AI Store Analyst', 'woowbot-woocommerce-chatbot' ); ?></h1>
		<div class="qcld-wia-wrap">
			<!-- Top Header Banner -->
			<header class="qcld-wia-header">
				<div class="qcld-wia-header-left">
					<div class="qcld-wia-badge">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>
						<?php esc_html_e( 'WooCommerce Intelligence', 'woowbot-woocommerce-chatbot' ); ?>
					</div>
					<div class="qcld-wia-title">
						<span class="qcld-wia-title-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
						</span>
						<?php esc_html_e( 'AI Store Analyst', 'woowbot-woocommerce-chatbot' ); ?>
					</div>
					<p class="qcld-wia-subtitle"><?php esc_html_e( 'Instant sales insights, predictive intelligence, and deep analytics powered by AI.', 'woowbot-woocommerce-chatbot' ); ?></p>
				</div>
				<div class="qcld-wia-header-actions">
					<div class="qcld-wia-provider-pill" title="<?php esc_attr_e( 'Active AI Provider', 'woowbot-woocommerce-chatbot' ); ?>">
						<span class="qcld-wia-live-dot"></span>
						<span class="qcld-wia-provider-name"><?php echo esc_html( $provider_name ); ?></span>
					</div>
					<a class="qcld-wia-btn qcld-wia-btn-outline" href="<?php echo esc_url( admin_url( 'admin.php?page=ai-tryon-for-woocommerce#ai_settings' ) ); ?>">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
						<?php esc_html_e( 'AI Settings', 'woowbot-woocommerce-chatbot' ); ?>
					</a>
				</div>
			</header>

			<!-- Main Layout Grid -->
			<div class="qcld-wia-layout">
				<!-- Left / Main Conversation Area -->
				<main class="qcld-wia-main">
					<div class="qcld-wia-card qcld-wia-chat-shell">
						<div class="qcld-wia-chat-toolbar">
							<div class="qcld-wia-toolbar-info">
								<div class="qcld-wia-bot-avatar">
									<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"></path><rect x="4" y="8" width="16" height="12" rx="2"></rect><path d="M2 14h2"></path><path d="M20 14h2"></path><path d="M9 13v2"></path><path d="M15 13v2"></path></svg>
								</div>
								<div>
									<div class="qcld-wia-toolbar-title"><?php esc_html_e( 'AI Store Analyst', 'woowbot-woocommerce-chatbot' ); ?></div>
									<div class="qcld-wia-toolbar-status"><?php esc_html_e( 'Ready to analyze your store', 'woowbot-woocommerce-chatbot' ); ?></div>
								</div>
							</div>
							<button type="button" id="qcld-wia-clear" class="qcld-wia-btn qcld-wia-btn-ghost" title="<?php esc_attr_e( 'Clear Conversation History', 'woowbot-woocommerce-chatbot' ); ?>">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
								<span><?php esc_html_e( 'Reset Chat', 'woowbot-woocommerce-chatbot' ); ?></span>
							</button>
						</div>

						<!-- Chat Messages Container -->
						<div id="qcld-wia-chat" class="qcld-wia-chat" aria-live="polite"></div>

						<!-- Prompt & Input Area -->
						<div class="qcld-wia-input-container">
							<form id="qcld-wia-form" class="qcld-wia-form">
								<label class="screen-reader-text" for="qcld-wia-question"><?php esc_html_e( 'Ask a question', 'woowbot-woocommerce-chatbot' ); ?></label>
								<div class="qcld-wia-input-wrap">
									<textarea id="qcld-wia-question" rows="1" maxlength="1000" required placeholder="<?php esc_attr_e( 'Ask about sales, best-selling products, customers, trends…', 'woowbot-woocommerce-chatbot' ); ?>"></textarea>
									<button class="qcld-wia-send-btn" type="submit" id="qcld-wia-submit" title="<?php esc_attr_e( 'Send question', 'woowbot-woocommerce-chatbot' ); ?>">
										<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
									</button>
								</div>
								<div class="qcld-wia-input-footer">
									<span><?php esc_html_e( 'Press Enter ↵ to send • Shift + Enter for new line', 'woowbot-woocommerce-chatbot' ); ?></span>
								</div>
							</form>
						</div>
					</div>
				</main>

				<!-- Right / Intelligence Sidebar -->
				<aside class="qcld-wia-sidebar" aria-label="<?php esc_attr_e( 'Store Insights', 'woowbot-woocommerce-chatbot' ); ?>">
					<!-- Growth Tip / Opportunity Highlight Card -->
					<div class="qcld-wia-card qcld-wia-tip-card">
						<div class="qcld-wia-tip-header">
							<div class="qcld-wia-tip-icon">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
							</div>
							<span class="qcld-wia-tip-tag"><?php esc_html_e( 'AI Growth Recommendation', 'woowbot-woocommerce-chatbot' ); ?></span>
						</div>
						<p id="qcld-wia-tip-text" class="qcld-wia-tip-body"><?php esc_html_e( 'Analyzing recent store orders and performance…', 'woowbot-woocommerce-chatbot' ); ?></p>
					</div>

					<!-- 30 Days KPI Cards -->
					<div class="qcld-wia-card qcld-wia-stats-card">
						<div class="qcld-wia-card-head">
							<h3><?php esc_html_e( 'Performance Overview', 'woowbot-woocommerce-chatbot' ); ?></h3>
							<span class="qcld-wia-period-badge"><?php esc_html_e( 'Last 30 Days', 'woowbot-woocommerce-chatbot' ); ?></span>
						</div>
						<div id="qcld-wia-metrics" class="qcld-wia-metrics-grid">
							<div class="qcld-wia-metric-skeleton"></div>
							<div class="qcld-wia-metric-skeleton"></div>
							<div class="qcld-wia-metric-skeleton"></div>
						</div>
					</div>

					<!-- Sales Trend Chart Card -->
					<div id="qcld-wia-trend-card" class="qcld-wia-card qcld-wia-chart-card">
						<div class="qcld-wia-card-head">
							<h3><?php esc_html_e( 'Revenue Trend', 'woowbot-woocommerce-chatbot' ); ?></h3>
						</div>
						<div id="qcld-wia-side-trend" class="qcld-wia-chart-body"></div>
					</div>

					<!-- Top Products Chart Card -->
					<div id="qcld-wia-products-card" class="qcld-wia-card qcld-wia-chart-card">
						<div class="qcld-wia-card-head">
							<h3><?php esc_html_e( 'Top Products by Revenue', 'woowbot-woocommerce-chatbot' ); ?></h3>
						</div>
						<div id="qcld-wia-side-products" class="qcld-wia-chart-body"></div>
					</div>
				</aside>
			</div>
		</div>
		</div>
		<?php
	}
}

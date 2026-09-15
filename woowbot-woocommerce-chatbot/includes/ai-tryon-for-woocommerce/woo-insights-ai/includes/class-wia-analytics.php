<?php
defined( 'ABSPATH' ) || exit;

final class QCLD_WIA_Analytics {
	const ALLOWED_STATUSES = array( 'wc-processing', 'wc-completed', 'wc-on-hold' );

	public function run( $name, array $arguments ) {
		switch ( $name ) {
			case 'get_recent_sales':
				return $this->get_recent_sales( isset( $arguments['limit'] ) ? (int) $arguments['limit'] : 5 );
			case 'get_sales_summary':
				return $this->get_sales_summary( $arguments['start_date'] ?? '', $arguments['end_date'] ?? '' );
			case 'get_top_products':
				return $this->get_top_products( $arguments['start_date'] ?? '', $arguments['end_date'] ?? '', isset( $arguments['limit'] ) ? (int) $arguments['limit'] : 5 );
			case 'get_sales_trend':
				return $this->get_sales_trend( $arguments['start_date'] ?? '', $arguments['end_date'] ?? '', $arguments['interval'] ?? 'day' );
			case 'compare_sales_periods':
				return $this->compare_sales_periods( $arguments );
			case 'get_coupon_report':
				return $this->get_coupon_report( $arguments['start_date'] ?? '', $arguments['end_date'] ?? '', $arguments['limit'] ?? 20 );
			case 'get_customer_report':
				return $this->get_customer_report( $arguments['start_date'] ?? '', $arguments['end_date'] ?? '', $arguments['limit'] ?? 20 );
			case 'get_product_performance':
				return $this->get_product_performance( $arguments['start_date'] ?? '', $arguments['end_date'] ?? '', $arguments['limit'] ?? 20 );
			case 'get_bought_together':
				return $this->get_bought_together( $arguments['start_date'] ?? '', $arguments['end_date'] ?? '', $arguments['limit'] ?? 20 );
			case 'get_sales_channels':
				return $this->get_sales_channels( $arguments['start_date'] ?? '', $arguments['end_date'] ?? '' );
			case 'get_store_anomalies':
				return $this->get_store_anomalies( $arguments['start_date'] ?? '', $arguments['end_date'] ?? '' );
			case 'get_dashboard_snapshot':
				return $this->get_dashboard_snapshot();
			default:
				return new WP_Error( 'qcld_wia_unknown_tool', __( 'The requested analysis is not available.', 'woowbot-woocommerce-chatbot' ) );
		}
	}

	private function base_args( $start_date = '', $end_date = '' ) {
		$args = array(
			'status'  => self::ALLOWED_STATUSES,
			'type'    => 'shop_order',
			'return'  => 'objects',
			'limit'   => -1,
			'paginate'=> false,
		);

		if ( $start_date || $end_date ) {
			$start = $this->valid_date( $start_date ) ?: '2000-01-01';
			$end   = $this->valid_date( $end_date ) ?: gmdate( 'Y-m-d' );
			$args['date_created'] = $start . ' 00:00:00...' . $end . ' 23:59:59';
		}
		return $args;
	}

	private function valid_date( $date ) {
		$date = sanitize_text_field( (string) $date );
		$parsed = DateTime::createFromFormat( 'Y-m-d', $date );
		return $parsed && $parsed->format( 'Y-m-d' ) === $date ? $date : '';
	}

	private function get_recent_sales( $limit ) {
		$args = $this->base_args();
		$args['limit'] = max( 1, min( 20, $limit ) );
		$args['orderby'] = 'date';
		$args['order'] = 'DESC';
		$orders = wc_get_orders( $args );
		$sales = array();

		foreach ( $orders as $order ) {
			$items = array();
			foreach ( $order->get_items() as $item ) {
				$items[] = array( 'product' => $item->get_name(), 'quantity' => $item->get_quantity() );
			}
			$sales[] = array(
				'order_id' => $order->get_id(),
				'order_number' => $order->get_order_number(),
				'order_reference' => '[[order:' . $order->get_id() . '|#' . $order->get_order_number() . ']]',
				'date'     => $order->get_date_created() ? $order->get_date_created()->date( 'c' ) : null,
				'total'    => (float) $order->get_total(),
				'currency' => $order->get_currency(),
				'items'    => $items,
			);
		}

		return array( 'sales' => $sales );
	}

	private function get_sales_summary( $start_date, $end_date ) {
		$orders = wc_get_orders( $this->base_args( $start_date, $end_date ) );
		$revenue = 0.0;
		$items = 0;
		$currency = get_woocommerce_currency();

		foreach ( $orders as $order ) {
			$revenue += (float) $order->get_total();
			$items += (int) $order->get_item_count();
			$currency = $order->get_currency() ?: $currency;
		}

		$count = count( $orders );
		return array(
			'start_date'          => $this->valid_date( $start_date ),
			'end_date'            => $this->valid_date( $end_date ),
			'order_count'         => $count,
			'items_sold'          => $items,
			'revenue'             => round( $revenue, wc_get_price_decimals() ),
			'average_order_value' => $count ? round( $revenue / $count, wc_get_price_decimals() ) : 0,
			'currency'            => $currency,
		);
	}

	private function get_top_products( $start_date, $end_date, $limit ) {
		$orders = wc_get_orders( $this->base_args( $start_date, $end_date ) );
		$products = array();

		foreach ( $orders as $order ) {
			foreach ( $order->get_items() as $item ) {
				$key = (string) ( $item->get_product_id() ?: $item->get_name() );
				if ( ! isset( $products[ $key ] ) ) {
					$products[ $key ] = array( 'product' => $item->get_name(), 'quantity' => 0, 'revenue' => 0.0 );
				}
				$products[ $key ]['quantity'] += (int) $item->get_quantity();
				$products[ $key ]['revenue'] += (float) $item->get_total();
			}
		}

		usort( $products, function ( $a, $b ) { return $b['revenue'] <=> $a['revenue']; } );
		return array( 'currency' => get_woocommerce_currency(), 'products' => array_slice( array_values( $products ), 0, max( 1, min( 20, $limit ) ) ) );
	}

	private function get_sales_trend( $start_date, $end_date, $interval ) {
		$interval = in_array( $interval, array( 'day', 'week', 'month' ), true ) ? $interval : 'day';
		$orders = wc_get_orders( $this->base_args( $start_date, $end_date ) );
		$points = array();

		foreach ( $orders as $order ) {
			$date = $order->get_date_created();
			if ( ! $date ) { continue; }
			$key = 'day' === $interval ? $date->date( 'Y-m-d' ) : ( 'week' === $interval ? $date->date( 'o-\\WW' ) : $date->date( 'Y-m' ) );
			if ( ! isset( $points[ $key ] ) ) { $points[ $key ] = array( 'period' => $key, 'orders' => 0, 'revenue' => 0.0 ); }
			$points[ $key ]['orders']++;
			$points[ $key ]['revenue'] += (float) $order->get_total();
		}

		ksort( $points );
		return array( 'interval' => $interval, 'currency' => get_woocommerce_currency(), 'points' => array_values( $points ) );
	}

	private function compare_sales_periods( array $arguments ) {
		$first = $this->get_sales_summary( $arguments['first_start'] ?? '', $arguments['first_end'] ?? '' );
		$second = $this->get_sales_summary( $arguments['second_start'] ?? '', $arguments['second_end'] ?? '' );
		$change = function ( $old, $new ) { return 0.0 === (float) $old ? null : round( ( ( $new - $old ) / $old ) * 100, 2 ); };
		return array(
			'first_period' => $first,
			'second_period' => $second,
			'changes_percent' => array(
				'revenue' => $change( $first['revenue'], $second['revenue'] ),
				'orders'  => $change( $first['order_count'], $second['order_count'] ),
				'aov'     => $change( $first['average_order_value'], $second['average_order_value'] ),
			),
		);
	}

	private function get_coupon_report( $start_date, $end_date, $limit ) {
		$coupons = array();
		$summary = array( 'coupon_orders' => 0, 'coupon_revenue' => 0.0, 'no_coupon_orders' => 0, 'no_coupon_revenue' => 0.0 );
		$has_costs = false;
		foreach ( wc_get_orders( $this->base_args( $start_date, $end_date ) ) as $order ) {
			$codes = $order->get_coupon_codes();
			if ( $codes ) { $summary['coupon_orders']++; $summary['coupon_revenue'] += (float) $order->get_total(); } else { $summary['no_coupon_orders']++; $summary['no_coupon_revenue'] += (float) $order->get_total(); }
			foreach ( $codes as $code ) {
				$key = strtolower( $code );
				if ( ! isset( $coupons[ $key ] ) ) { $coupons[ $key ] = array( 'code' => $code, 'orders' => 0, 'revenue' => 0.0, 'discount' => 0.0, 'product_cost' => 0.0, 'cost_known' => true, 'recent_orders' => array() ); }
				$coupons[ $key ]['orders']++;
				$coupons[ $key ]['revenue'] += (float) $order->get_total();
				$coupons[ $key ]['discount'] += (float) $order->get_discount_total();
				if ( count( $coupons[ $key ]['recent_orders'] ) < 5 ) { $coupons[ $key ]['recent_orders'][] = '[[order:' . $order->get_id() . '|#' . $order->get_order_number() . ']]'; }
				foreach ( $order->get_items() as $item ) {
					$cost = method_exists( $item, 'get_cogs_value' ) ? (float) $item->get_cogs_value() : (float) $item->get_meta( '_cogs_value', true );
					if ( $cost > 0 ) { $has_costs = true; $coupons[ $key ]['product_cost'] += $cost; } else { $coupons[ $key ]['cost_known'] = false; }
				}
			}
		}
		foreach ( $coupons as &$coupon ) { $coupon['average_order_value'] = $coupon['orders'] ? round( $coupon['revenue'] / $coupon['orders'], 2 ) : 0; $coupon['estimated_profit'] = $coupon['cost_known'] ? round( $coupon['revenue'] - $coupon['product_cost'], 2 ) : null; }
		unset( $coupon );
		usort( $coupons, function ( $a, $b ) { return $b['revenue'] <=> $a['revenue']; } );
		$summary['coupon_aov'] = $summary['coupon_orders'] ? round( $summary['coupon_revenue'] / $summary['coupon_orders'], 2 ) : 0; $summary['no_coupon_aov'] = $summary['no_coupon_orders'] ? round( $summary['no_coupon_revenue'] / $summary['no_coupon_orders'], 2 ) : 0;
		return array( 'currency' => get_woocommerce_currency(), 'cost_data_available' => $has_costs, 'summary' => $summary, 'coupons' => array_slice( array_values( $coupons ), 0, max( 1, min( 50, (int) $limit ) ) ) );
	}

	private function get_customer_report( $start_date, $end_date, $limit ) {
		$customers = array();
		foreach ( wc_get_orders( $this->base_args( $start_date, $end_date ) ) as $order ) {
			$id = (int) $order->get_customer_id();
			$key = $id ? 'customer-' . $id : 'guest-' . substr( wp_hash( strtolower( (string) $order->get_billing_email() ) ), 0, 10 );
			$name = trim( $order->get_formatted_billing_full_name() );
			if ( '' === $name ) { $name = $order->get_billing_company() ?: ( $order->get_billing_email() ?: 'Guest customer' ); }
			$name = str_replace( array( '[', ']', '|' ), '', $name );
			$reference = $id ? '[[customer:' . $id . '|' . $name . ']]' : $name;
			if ( ! isset( $customers[ $key ] ) ) { $customers[ $key ] = array( 'customer_id' => $id ?: null, 'customer' => $reference, 'name' => $name, 'email' => $order->get_billing_email(), 'orders' => 0, 'revenue' => 0.0, 'last_order' => '', 'last_order_reference' => '', 'order_references' => array() ); }
			$customers[ $key]['orders']++;
			$customers[ $key ]['revenue'] += (float) $order->get_total();
			if ( count( $customers[ $key ]['order_references'] ) < 10 ) { $customers[ $key ]['order_references'][] = '[[order:' . $order->get_id() . '|#' . $order->get_order_number() . ']]'; }
			$date = $order->get_date_created();
			if ( $date && $date->date( 'Y-m-d' ) > $customers[ $key ]['last_order'] ) { $customers[ $key ]['last_order'] = $date->date( 'Y-m-d' ); $customers[ $key ]['last_order_reference'] = '[[order:' . $order->get_id() . '|#' . $order->get_order_number() . ']]'; }
		}
		foreach ( $customers as &$customer ) { $customer['average_order_value'] = $customer['orders'] ? round( $customer['revenue'] / $customer['orders'], 2 ) : 0; $customer['repeat_buyer'] = $customer['orders'] > 1; $customer['days_since_last_order'] = $customer['last_order'] ? max( 0, (int) floor( ( current_time( 'timestamp' ) - strtotime( $customer['last_order'] ) ) / DAY_IN_SECONDS ) ) : null; }
		unset( $customer );
		usort( $customers, function ( $a, $b ) { return $b['revenue'] <=> $a['revenue']; } );
		return array( 'currency' => get_woocommerce_currency(), 'customers' => array_slice( array_values( $customers ), 0, max( 1, min( 50, (int) $limit ) ) ) );
	}

	private function get_product_performance( $start_date, $end_date, $limit ) {
		$products = array();
		$has_costs = false;
		foreach ( wc_get_orders( $this->base_args( $start_date, $end_date ) ) as $order ) {
			foreach ( $order->get_items() as $item ) {
				$key = (string) ( $item->get_product_id() ?: $item->get_name() );
				if ( ! isset( $products[ $key ] ) ) { $products[ $key ] = array( 'product' => $item->get_name(), 'quantity' => 0, 'orders' => 0, 'revenue' => 0.0, 'cost' => 0.0, 'cost_known' => true ); }
				$products[ $key ]['quantity'] += (int) $item->get_quantity(); $products[ $key ]['orders']++; $products[ $key ]['revenue'] += (float) $item->get_total();
				$cost = method_exists( $item, 'get_cogs_value' ) ? (float) $item->get_cogs_value() : (float) $item->get_meta( '_cogs_value', true );
				if ( $cost > 0 ) { $has_costs = true; $products[ $key ]['cost'] += $cost; } else { $products[ $key ]['cost_known'] = false; }
			}
		}
		foreach ( $products as &$product ) { $product['profit'] = $product['cost_known'] ? round( $product['revenue'] - $product['cost'], 2 ) : null; $product['margin_percent'] = $product['cost_known'] && $product['revenue'] > 0 ? round( $product['profit'] / $product['revenue'] * 100, 2 ) : null; }
		unset( $product );
		usort( $products, function ( $a, $b ) { return $b['revenue'] <=> $a['revenue']; } );
		$count = max( 1, min( 50, (int) $limit ) ); $top = array_slice( array_values( $products ), 0, $count ); $underperforming = array_slice( array_reverse( array_values( $products ) ), 0, $count );
		$profitable = array_values( array_filter( $products, function ( $product ) { return null !== $product['profit']; } ) ); usort( $profitable, function ( $a, $b ) { return $b['profit'] <=> $a['profit']; } );
		return array( 'currency' => get_woocommerce_currency(), 'cost_data_available' => $has_costs, 'products' => $top, 'most_profitable' => array_slice( $profitable, 0, $count ), 'underperforming' => $underperforming );
	}

	private function get_bought_together( $start_date, $end_date, $limit ) {
		$pairs = array();
		foreach ( wc_get_orders( $this->base_args( $start_date, $end_date ) ) as $order ) {
			$names = array_values( array_unique( array_map( function ( $item ) { return $item->get_name(); }, $order->get_items() ) ) );
			sort( $names );
			for ( $i = 0; $i < count( $names ); $i++ ) { for ( $j = $i + 1; $j < count( $names ); $j++ ) { $key = $names[ $i ] . '|' . $names[ $j ]; if ( ! isset( $pairs[ $key ] ) ) { $pairs[ $key ] = array( 'product_a' => $names[ $i ], 'product_b' => $names[ $j ], 'orders_together' => 0 ); } $pairs[ $key ]['orders_together']++; } }
		}
		usort( $pairs, function ( $a, $b ) { return $b['orders_together'] <=> $a['orders_together']; } );
		return array( 'pairs' => array_slice( array_values( $pairs ), 0, max( 1, min( 50, (int) $limit ) ) ) );
	}

	private function get_sales_channels( $start_date, $end_date ) {
		$channels = array();
		foreach ( wc_get_orders( $this->base_args( $start_date, $end_date ) ) as $order ) {
			$source = $order->get_meta( '_wc_order_attribution_utm_source', true ) ?: $order->get_meta( '_wc_order_attribution_source_type', true );
			$source = $source ?: $order->get_created_via(); $source = $source ?: 'unknown';
			if ( ! isset( $channels[ $source ] ) ) { $channels[ $source ] = array( 'channel' => $source, 'orders' => 0, 'revenue' => 0.0 ); }
			$channels[ $source ]['orders']++; $channels[ $source ]['revenue'] += (float) $order->get_total();
		}
		usort( $channels, function ( $a, $b ) { return $b['revenue'] <=> $a['revenue']; } );
		return array( 'currency' => get_woocommerce_currency(), 'channels' => array_values( $channels ) );
	}

	private function get_store_anomalies( $start_date, $end_date ) {
		$trend = $this->get_sales_trend( $start_date, $end_date, 'day' ); $values = array_column( $trend['points'], 'revenue' );
		if ( count( $values ) < 3 ) { return array( 'anomalies' => array(), 'note' => 'At least three active sales days are required.' ); }
		$mean = array_sum( $values ) / count( $values ); $variance = array_sum( array_map( function ( $value ) use ( $mean ) { return pow( $value - $mean, 2 ); }, $values ) ) / count( $values ); $sd = sqrt( $variance ); $anomalies = array();
		foreach ( $trend['points'] as $point ) { $z = $sd > 0 ? ( $point['revenue'] - $mean ) / $sd : 0; if ( abs( $z ) >= 2 ) { $anomalies[] = array( 'date' => $point['period'], 'revenue' => $point['revenue'], 'direction' => $z > 0 ? 'spike' : 'drop', 'standard_deviations' => round( abs( $z ), 2 ) ); } }
		return array( 'currency' => get_woocommerce_currency(), 'daily_average' => round( $mean, 2 ), 'anomalies' => $anomalies );
	}

	private function get_dashboard_snapshot() {
		$today = current_time( 'Y-m-d' );
		$current_start = wp_date( 'Y-m-d', strtotime( '-29 days', current_time( 'timestamp' ) ), wp_timezone() );
		$previous_end = wp_date( 'Y-m-d', strtotime( '-30 days', current_time( 'timestamp' ) ), wp_timezone() );
		$previous_start = wp_date( 'Y-m-d', strtotime( '-59 days', current_time( 'timestamp' ) ), wp_timezone() );
		$current = $this->get_sales_summary( $current_start, $today );
		$previous = $this->get_sales_summary( $previous_start, $previous_end );
		$trend_start = wp_date( 'Y-m-d', strtotime( '-13 days', current_time( 'timestamp' ) ), wp_timezone() );
		$trend = $this->get_sales_trend( $trend_start, $today, 'day' );
		$products = $this->get_top_products( $current_start, $today, 5 );
		$revenue_change = $previous['revenue'] > 0 ? round( ( $current['revenue'] - $previous['revenue'] ) / $previous['revenue'] * 100, 1 ) : null;
		$order_change = $previous['order_count'] > 0 ? round( ( $current['order_count'] - $previous['order_count'] ) / $previous['order_count'] * 100, 1 ) : null;
		$aov_change = $previous['average_order_value'] > 0 ? round( ( $current['average_order_value'] - $previous['average_order_value'] ) / $previous['average_order_value'] * 100, 1 ) : null;
		if ( 0 === $current['order_count'] ) {
			$tip = __( 'No qualifying orders were found in the last 30 days. Check traffic, checkout errors, and order-status settings first.', 'woowbot-woocommerce-chatbot' );
		} elseif ( null !== $order_change && $order_change < -5 && ( null === $aov_change || $order_change < $aov_change ) ) {
			/* translators: %s: Order percentage change. */
			$tip = sprintf( __( 'Orders are down %s%%. Focus on recovering traffic and repeat buyers; ask the analyst which customers are most likely to return.', 'woowbot-woocommerce-chatbot' ), abs( $order_change ) );
		} elseif ( null !== $aov_change && $aov_change < -5 ) {
			/* translators: %s: Average order value percentage change. */
			$tip = sprintf( __( 'Average order value is down %s%%. Test bundles using the products-bought-together report.', 'woowbot-woocommerce-chatbot' ), abs( $aov_change ) );
		} elseif ( ! empty( $products['products'][0]['product'] ) ) {
			/* translators: %s: Product name. */
			$tip = sprintf( __( '%s leads recent revenue. Pair it with frequently bought products and feature the bundle prominently.', 'woowbot-woocommerce-chatbot' ), $products['products'][0]['product'] );
		} else {
			$tip = __( 'Review recent customers, coupons, and sales channels to find the clearest next growth opportunity.', 'woowbot-woocommerce-chatbot' );
		}
		return array( 'period' => array( 'start' => $current_start, 'end' => $today ), 'summary' => $current, 'changes' => array( 'revenue' => $revenue_change, 'orders' => $order_change, 'aov' => $aov_change ), 'tip' => $tip, 'trend' => $trend, 'products' => $products );
	}
}

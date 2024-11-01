<?php
/**
 * Cart functions
 *
 * Configure cart with settings
 *
 * @author   Manuel Muñoz <mmr010496@gmail.com
 * @category WordPress
 * @package  Plugin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Automatically add product to cart on visit
 */
add_action( 'template_redirect', 'fwc_add_product_to_cart' );
/**
 * Add products to cart.
 *
 * @return void
 */
function fwc_add_product_to_cart() {
	if ( ! is_admin() ) {
		$settings = get_option('fwc_settings');
		if ( 'on' === $settings['fwc_setting_fill_cart'] ) {
			$productos = isset( $settings['fwc_setting_products'] ) ? $settings['fwc_setting_products'] : '';
			if ( '' !== $productos && ! empty( $productos ) ) {
				if ( ! isset( $_COOKIE['_wp_fwc_first'] ) ) {
					$time = (10 * 365 * 24 * 60 * 60);
					if ( ! empty( $settings['fwc_setting_fill_cart_time'] ) && '' !== $settings['fwc_setting_fill_cart_time'] && 0 !== $settings['fwc_setting_fill_cart_time'] ) {
						$time = $settings['fwc_setting_fill_cart_time'];
					}
					setcookie( '_wp_fwc_first', 1, time() + ( $time ), COOKIEPATH, COOKIE_DOMAIN, false );
					fwc_refill_cart( $productos );
				}
			}
		}
		if ( 'on' === $_GET['fwc_fill_cart'] && 'on' === $settings['fwc_setting_fill_cart_link_check'] ) {
			$productos = isset( $settings['fwc_setting_products'] ) ? $settings['fwc_setting_products'] : '';
			if ( '' !== $productos && ! empty( $productos ) ) {
				fwc_refill_cart( $productos );
			}
		}
	}
}

/**
 * Refill cart
 *
 * @param array $productos array products.
 * @return void
 */
function fwc_refill_cart( $productos ) {
	$statistics = get_option('fwc_settings_statistics');
	if ( 'on' === $statistics['fwc_save_statistics'] ) {
		$option_count = get_option( 'fwc_count_times' );
		if ( false === $option_count ) {
			add_option( 'fwc_count_times', 1 );
		} else {
			$option_updated = (int) $option_count + 1;
			update_option( 'fwc_count_times', $option_updated );
		}
		$option_count = get_option( 'fwc_count_users' );
		$usuario_info = array(
			'ip'   => isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '',
			'pc'   => isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '',
			'date' => date( get_option( 'date_format' ) . ' G:i:s' ),
		);
		if ( is_user_logged_in() ) {
			$usuario_info['id'] =  get_current_user_id();
		}
		if ( false === $option_count ) {
			$array_users   = array();
			$array_users[] = $usuario_info;
			add_option( 'fwc_count_users', $array_users );
		} else {
			$option_count[] = $usuario_info;
			$option_updated = $option_count;
			update_option( 'fwc_count_users', $option_updated );
		}
	}
	foreach ( $productos as $key => $product_id ) {
		$found = false;
		// Check if product already in cart.
		if ( sizeof( WC()->cart->get_cart() ) > 0 ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
				$_product = $values['data'];
				if ( $_product->get_id() == $product_id )
					$found = true;
			}
			// If product not found, add it.
			if ( ! $found ) {
				WC()->cart->add_to_cart( $product_id );
			}
		} else {
			// If no products in cart, add it.
			WC()->cart->add_to_cart( $product_id );
		}
	}
}

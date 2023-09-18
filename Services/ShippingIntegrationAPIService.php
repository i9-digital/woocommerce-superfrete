<?php

namespace Superfrete\Services;

class ShippingSuperfreteService {

	/**
	 * function to get service codes enableds on WooCommerce.
	 *
	 * @return array
	 */
	public function getCodesEnableds() {
		return $this->getCodesWcShippingClass();
	}

	/**
	 * Function to search the codes of services available in woocommerce in string format separated by commas
	 *
	 * @return string
	 */
	public function getStringCodesEnables() {
		return implode( ',', $this->getCodesEnableds() );
	}

	/**
	 * Function to search the codes of services available in woocommerce
	 *
	 * @return array
	 */
	public function getCodesWcShippingClass() {
		$shippings = WC()->shipping->get_shipping_methods();

		if ( is_null( $shippings ) ) {
			return array();
		}

		$codes = array();

		foreach ( $shippings as $method ) {
			if ( ! isset( $method->code ) || is_null( $method->code ) ) {
				continue;
			}
			$codes[] = $method->code;
		}

		return $codes;
	}

	/**
	 * Function to return the "SuperFrete" shipping methods used
	 * in the delivery zones
	 *
	 * @return array
	 */
	public function getMethodsActivedsSuperfrete() {
		$methods        = array();
		$delivery_zones = \WC_Shipping_Zones::get_zones();
		foreach ( $delivery_zones as $zone ) {
			foreach ( $zone['shipping_methods'] as $method ) {
				if ( ! $this->isSuperfreteMethod( $method ) && 'yes' != $method->enabled ) {
					continue;
				}
				$methods[] = $method;
			}
		}
		return $methods;
	}

	/**
	 * Function to check if the method is SuperFrete.
	 *
	 * @param object $method
	 * @return boolean
	 */
	public function isSuperfreteMethod( $method ) {
		return ( is_numeric( strpos( $method->id, 'superfrete_' ) ) );
	}
}

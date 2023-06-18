<?php

namespace IntegrationAPI\Services;

use IntegrationAPI\Models\Method;
use IntegrationAPI\Models\Session;

/**
 * Class responsible for managing the shipping method options service
 */
class OptionsMethodShippingService {


	/**
	 * Registry key of the send method options saved in the database
	 */
	const KEY_OPTIONS_METHOD_DATABASE = 'integration_api_option_method_shipment_';

	/**
	 * Function to return the options
	 * (own hands, acknowledgment of receipt, collection, extra fees, extra time)
	 * of the SuperFrete shipping methods
	 *
	 * @return array
	 */
	public function get() {
		$methods = array();

		$options = $this->getOptionsShipments();

		$enableds = ( new Method() )->getArrayShippingMethodsEnabledByZoneIntegrationAPI();

		$shippingMethods = \WC()->shipping->get_shipping_methods();

		foreach ( $shippingMethods as $method ) {
			if ( ! $this->isMethodIntegrationAPI( $method ) ) {
				continue;
			}

			if ( in_array( $method->id, $enableds ) ) {
				$data      = array_filter(
					$options,
					function ( $option ) use ( $method ) {
						if ( $option['id'] == $method->code ) {
							$option['tax']  = ( ! empty( $option['tax'] ) ) ? floatval( $option['tax'] ) : 0;
							$option['time'] = ( ! empty( $option['time'] ) ) ? floatval( $option['time'] ) : 0;
							return $option;
						}
					}
				);
				$methods[] = end( $data );
			}
		}

		return $methods;
	}

	/**
	 * Function to return the options
	 * (own hands, acknowledgment of receipt, collection, extra fees, extra time)
	 * on database of the SuperFrete shipping methods
	 *
	 * @return array
	 */
	public function getOptionsShipments() {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT * FROM $wpdb->options WHERE option_name like %s",
			'%' . $wpdb->esc_like( self::KEY_OPTIONS_METHOD_DATABASE ) . '%'
		);

		$results = $wpdb->get_results( $sql );

		if ( empty( $results ) ) {
			return array();
		}

		$options = array_map(
			function ( $option ) {
				if ( ! empty( $option->option_value ) ) {
					$data = unserialize( $option->option_value );
					if ( ! empty( $data['id'] ) ) {
						$data['code_modal'] = 'code_shiping_' . $data['id'];
						$data['code']       = $data['id'];
						return $data;
					}
				}
			},
			$results
		);

		$codeStore = hash( 'sha512', get_option( 'home' ) );

		$_SESSION[ Session::ME_KEY ][ $codeStore ]['integrationapi_options'] = $options;

		return $options;
	}

	/**
	 * Function to check if the method is SuperFrete.
	 *
	 * @param object $method
	 * @return boolean
	 */
	public function isMethodIntegrationAPI( $method ) {
		return ( is_numeric( strpos( $method->id, 'integrationapi_' ) ) );
	}
}

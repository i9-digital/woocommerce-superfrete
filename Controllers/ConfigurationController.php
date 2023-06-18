<?php

namespace IntegrationAPI\Controllers;

use IntegrationAPI\Helpers\SanitizeHelper;
use IntegrationAPI\Helpers\WpNonceValidatorHelper;
use IntegrationAPI\Models\Address;
use IntegrationAPI\Models\Agency;
use IntegrationAPI\Models\Store;
use IntegrationAPI\Models\Method;
use IntegrationAPI\Models\Option;
use IntegrationAPI\Services\ConfigurationsService;
use IntegrationAPI\Services\OptionsMethodShippingService;

/**
 * Class responsible for the configuration controller
 */
class ConfigurationController {

	/**
	 * Function to get configurations of user
	 *
	 * @return json
	 */
	public function getConfigurations() {

		WpNonceValidatorHelper::check( $_GET['_wpnonce'], 'save_sf_configurations' );

		return wp_send_json(
			( new ConfigurationsService() )->getConfigurations(),
			200
		);
	}

	/**
	 * Function to obtain which hook the calculator will
	 * be displayed on the product screen
	 *
	 * @return string
	 */
	public function getWhereCalculatorValue() {
		$option = get_option( 'integration_api_option_where_show_calculator' );
		if ( ! $option ) {
			return 'woocommerce_before_add_to_cart_button';
		}
		return $option;
	}

	/**
	 * Function to save all configs
	 *
	 * @param Array $data
	 * @return json
	 */
	public function saveAll() {

		WpNonceValidatorHelper::check( $_POST['_wpnonce'], 'save_sf_configurations' );

		$response = ( new ConfigurationsService() )->saveConfigurations( SanitizeHelper::apply( $_POST ) );
		return wp_send_json( $response, 200 );
	}
}

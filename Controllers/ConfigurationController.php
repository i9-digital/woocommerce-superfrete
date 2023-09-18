<?php

namespace Superfrete\Controllers;

use Superfrete\Helpers\SanitizeHelper;
use Superfrete\Helpers\WpNonceValidatorHelper;
use Superfrete\Models\Address;
use Superfrete\Models\Agency;
use Superfrete\Models\Store;
use Superfrete\Models\Method;
use Superfrete\Models\Option;
use Superfrete\Services\ConfigurationsService;
use Superfrete\Services\OptionsMethodShippingService;

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

		WpNonceValidatorHelper::check( $_GET['_wpnonce'], 'save_superfrete_configurations' );

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
		$option = get_option( 'superfrete_option_where_show_calculator' );
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

		WpNonceValidatorHelper::check( $_POST['_wpnonce'], 'save_superfrete_configurations' );

		$response = ( new ConfigurationsService() )->saveConfigurations( SanitizeHelper::apply( $_POST ) );
		return wp_send_json( $response, 200 );
	}
}

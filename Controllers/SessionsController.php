<?php

namespace IntegrationAPI\Controllers;

use IntegrationAPI\Services\ClearDataStored;
use IntegrationAPI\Helpers\SessionHelper;
use IntegrationAPI\Helpers\WpNonceValidatorHelper;
use IntegrationAPI\Models\Session;

class SessionsController {

	/**
	 * Function to get information from the plugin session
	 *
	 * @return json
	 */
	public function getSession() {
		SessionHelper::initIfNotExists();

		return wp_send_json( $_SESSION[ Session::ME_KEY ], 200 );
	}

	/**
	 * Function to delete information from the plugin session
	 *
	 * @return json
	 */
	public function deleteSession() {
		WpNonceValidatorHelper::check( $_GET['_wpnonce'], 'save_sf_configurations' );
		( new ClearDataStored() )->clear();
	}
}

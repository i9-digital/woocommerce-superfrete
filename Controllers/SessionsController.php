<?php

namespace Superfrete\Controllers;

use Superfrete\Services\ClearDataStored;
use Superfrete\Helpers\SessionHelper;
use Superfrete\Helpers\WpNonceValidatorHelper;
use Superfrete\Models\Session;

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
		WpNonceValidatorHelper::check( $_GET['_wpnonce'], 'save_superfrete_configurations' );
		( new ClearDataStored() )->clear();
	}
}

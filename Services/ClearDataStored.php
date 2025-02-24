<?php

namespace Superfrete\Services;

use Superfrete\Models\Seller;
use Superfrete\Models\Session;
use Superfrete\Models\ShippingService;
use Superfrete\Helpers\SessionHelper;

class ClearDataStored {

	public function __construct() {
		SessionHelper::initIfNotExists();
	}
	/**
	 * Function to clear data about seller stored in session or database.
	 *
	 * @return void
	 */
	public function clear() {
        @$session = $_SESSION;
        unset($session['quotation-superfrete']);

		( new Seller() )->destroy();
		( new ShippingService() )->destroy();

		if (  empty( $_SESSION[ Session::ME_KEY ] ) ) {
            return false;
        }

        foreach ( $_SESSION[ Session::ME_KEY ] as $hash => $item) {

            if ( $hash != 'notices_superfrete' ) {
                
                if (!$this->hasDataOnSession($hash)) {
                    continue;
                }

                unset( $_SESSION[ Session::ME_KEY ][ $hash ] );
            }
        }
	}

    /**
     * @param string $hash
     * @return bool
     */
    private function hasDataOnSession($hash)
    {
        $hasData = true;
        if (empty($_SESSION)) {
            $hasData = false;
        }

        if (empty($_SESSION[ Session::ME_KEY ])) {
            $hasData = false;
        }

        if (empty($_SESSION[ Session::ME_KEY ][ $hash ])) {
            $hasData = false;
        }

        return $hasData;
    }
}

<?php

if ( class_exists( 'WC_Integration_API_Shipping' ) ) {
	class WC_Integration_API_Shipping_Via_Brasil_Aereo extends WC_Integration_API_Shipping {

		const ID = 'integrationapi_via_brasil_aereo';

		const TITLE = 'Via Brasil Aereo';

		const METHOD_TITLE = 'Via Brasil Aereo (SuperFrete)';

		public $code = 8;

		public $company = 'Via Brasil';

		/**
		 * Initialize Via Brasil Aereo.
		 *
		 * @param int $instance_id Shipping zone instance.
		 */
		public function __construct( $instance_id = 0 ) {
			$this->id           = self::ID;
			$this->method_title = self::METHOD_TITLE;
			parent::__construct( $instance_id );
		}
	}
}

<?php

if ( class_exists( 'WC_Integration_API_Shipping' ) ) {
	class WC_Integration_API_Shipping_Jadlog_Com extends WC_Integration_API_Shipping {

		const ID = 'integrationapi_jadlog_com';

		const TITLE = 'Jadlog .Com';

		const METHOD_TITLE = 'Jadlog .Com (SuperFrete)';

		public $code = 4;

		public $company = 'Jadlog';

		/**
		 * Initialize Jadlog .COm.
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

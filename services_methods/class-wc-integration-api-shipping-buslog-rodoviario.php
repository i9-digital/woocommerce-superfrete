<?php

use IntegrationAPI\Models\ShippingService;

if ( class_exists( 'WC_Integration_API_Shipping' ) ) {
	class WC_Integration_API_Shipping_Buslog_Rodoviario extends WC_Integration_API_Shipping {

		const ID = 'integrationapi_buslog_rodoviario';

		const TITLE = 'Buslog Rodoviário';

		const METHOD_TITLE = 'Buslog Rodoviário (SuperFrete)';

		public $code = ShippingService::BUSLOG_RODOVIARIO;

		public $company = 'Buslog';

		/**
		 * Initialize Buslog Rodoviário.
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

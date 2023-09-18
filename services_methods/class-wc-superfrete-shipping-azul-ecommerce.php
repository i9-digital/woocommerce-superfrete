<?php

if ( class_exists( 'WC_Superfrete_Shipping' ) ) {
	class WC_Superfrete_Shipping_Azul_Ecommerce extends WC_Superfrete_Shipping {

		const ID = 'superfrete_azul_ecommerce';

		const TITLE = 'Azul Ecommerce';

		const METHOD_TITLE = 'Azul Ecommerce (SuperFrete)';

		public $code = 16;

		public $company = 'Azul Cargo Express';

		/**
		 * Initialize Azul Ecommerce.
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

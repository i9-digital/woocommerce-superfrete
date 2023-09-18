<?php

if ( class_exists( 'WC_Superfrete_Shipping' ) ) {
	class WC_Superfrete_Shipping_Azul_Amanha extends WC_Superfrete_Shipping {

		const ID = 'superfrete_azul_amanha';

		const TITLE = 'Azul Amanhã';

		const METHOD_TITLE = 'Azul Amanhã (SuperFrete)';

		public $code = 15;

		public $company = 'Azul Cargo Express';

		/**
		 * Initialize Azul Amanhã.
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

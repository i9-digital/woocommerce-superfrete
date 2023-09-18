<?php

if ( class_exists( 'WC_Superfrete_Shipping' ) ) {
	class WC_Superfrete_Shipping_Correios_Sedex extends WC_Superfrete_Shipping {

		const ID = 'superfrete_correios_sedex';

		const TITLE = 'Correios Sedex';

		const METHOD_TITLE = 'Correios Sedex (SuperFrete)';

		public $code = 2;

		public $company = 'Correios';

		/**
		 * Initialize Correios Sedex.
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

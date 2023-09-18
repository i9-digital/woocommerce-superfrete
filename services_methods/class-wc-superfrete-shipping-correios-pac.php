<?php

if ( class_exists( 'WC_Superfrete_Shipping' ) ) {
	class WC_Superfrete_Shipping_Correios_Pac extends WC_Superfrete_Shipping {

		const ID = 'superfrete_correios_pac';

		const TITLE = 'Correios Pac';

		const METHOD_TITLE = 'Correios Pac (SuperFrete)';

		public $code = 1;

		public $company = 'Correios';

		/**
		 * Initialize Correios Pac.
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

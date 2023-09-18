<?php

if ( class_exists( 'WC_Superfrete_Shipping' ) ) {
	class WC_Superfrete_Shipping_Latam_Juntos extends WC_Superfrete_Shipping {

		const ID = 'superfrete_latam_juntos';

		const TITLE = 'LATAM Cargo éFácil';

		const METHOD_TITLE = 'LATAM Cargo éFácil (SuperFrete)';

		public $code = 12;

		public $company = 'LATAM Cargo';

		/**
		 * Initialize Latam.
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

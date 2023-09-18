<?php

if ( class_exists( 'WC_Superfrete_Shipping' ) ) {
	class WC_Superfrete_Shipping_Latam extends WC_Superfrete_Shipping {

		const ID = 'superfrete_latam';

		const TITLE = 'Latam';

		const METHOD_TITLE = 'Latam (SuperFrete)';

		public $code = 10;

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

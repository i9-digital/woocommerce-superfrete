<?php

if ( class_exists( 'WC_Superfrete_Shipping' ) ) {
	class WC_Superfrete_Shipping_Jadlog_Com extends WC_Superfrete_Shipping {

		const ID = 'superfrete_jadlog_com';

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

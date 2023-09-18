<?php

if ( class_exists( 'WC_Superfrete_Shipping' ) ) {
	class WC_Superfrete_Shipping_Jadlog_Package extends WC_Superfrete_Shipping {

		const ID = 'superfrete_jadlog_package';

		const TITLE = 'Jadlog Package';

		const METHOD_TITLE = 'Jadlog Package (SuperFrete)';

		public $code = 3;

		public $company = 'Jadlog';

		/**
		 * Initialize Jadlog Package.
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

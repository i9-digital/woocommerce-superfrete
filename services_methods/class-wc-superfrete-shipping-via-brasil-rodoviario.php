<?php

if ( class_exists( 'WC_Superfrete_Shipping' ) ) {
	class WC_Superfrete_Shipping_Via_Brasil_Rodoviario extends WC_Superfrete_Shipping {

		const ID = 'superfrete_via_brasil_rodoviario';

		const TITLE = 'Via Brasil Rodoviário';

		const METHOD_TITLE = 'Via Brasil Rodoviário (SuperFrete)';

		public $code = 9;

		public $company = 'Via Brasil';

		/**
		 * Initialize Via Brasil Rodoviário.
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

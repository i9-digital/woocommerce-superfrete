<?php

namespace Superfrete\Models;

use Superfrete\Models\Address;

class CalculatorShow {

	/**
	 * @return bool
	 */
	public function get() {
		$show = get_option( 'superfrete_hide_calculator_product' );

		if ( ! $show ) {
			return true;
		}

		if ( $show == '1' ) {
			return false;
		}

		return false;
	}

	/**
	 * @param String $value
	 * @return bool
	 */
	public function set( $value ) {
		if ( $value == 'true' ) {
			delete_option( 'superfrete_hide_calculator_product' );
			return true;
		} else {
			add_option( 'superfrete_hide_calculator_product', 1 );
			return false;
		}
	}
}

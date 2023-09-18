<?php

namespace Superfrete\Models;

class Log {

	/**
	 * @return void
	 */
	public function register( $id, $type, $body, $response ) {
		$log = array(
			'id'       => $id,
			'date'     => date( 'Y-m-d h:i:s' ),
			'type'     => $type,
			'body'     => $body,
			'response' => $response,
		);

		add_post_meta( $id, 'superfrete_log_order', $log );
	}

	public function getRegister( $id ) {
		$logs = get_post_meta( $id, 'superfrete_log_order' );

		$response = array();

		foreach ( $logs as $log ) {
			$response[] = $log;
		}

		return $response;
	}
}

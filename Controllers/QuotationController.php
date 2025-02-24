<?php

namespace Superfrete\Controllers;

use Superfrete\Helpers\SanitizeHelper;
use Superfrete\Services\PayloadService;
use Superfrete\Services\QuotationService;
use Superfrete\Services\QuotationProductPageService;
use Superfrete\Models\Session;

/**
 * Class responsible for the quotation controller
 */
class QuotationController {

	/**
	 * Construct of CotationController
	 */
	public function __construct() {
		add_action(
			'woocommerce_checkout_order_processed',
			array(
				$this,
				'makeCotationOrder',
			)
		);
	}

	/**
	 * Function to make a quotation by order woocommerce
	 *
	 * @param int $postId
	 * @return void
	 */
	public function makeCotationOrder( $postId ) {
		$result = ( new QuotationService() )->calculateQuotationByPostId( $postId );

		if ( !empty($result->errors) ) {
			$myErrors = '';
			foreach($result->errors as $myError) {
				$myErrors .= '<li style="margin:6px"><strong>' . $myError . '</strong></li>';	
			}

			$myErrorMainMessage = (isset($result->message)) ? '<li style="margin-bottom:6px"><strong>' . $result->message . '</strong></li>' : false;

			return wp_send_json(
				array(
					'result' => 'failure',
					'messages' => '<ul class="woocommerce-error" role="alert">'. $myErrorMainMessage . $myErrors . '</ul>',
					'refresh' => false,
					'reload' => false					
				),
				200
			);	
		} else {
			( new PayloadService() )->save( $postId );
		}

		if ( ! empty( $_SESSION[ Session::ME_KEY ]['quotation'] ) ) {
			unset( $_SESSION[ Session::ME_KEY ]['quotation'] );
		}

		return $result;
	}

	/**
	 * Function to refresh quotation
	 *
	 * @return json
	 */
	public function refreshCotation() {
		$results = $this->makeCotationOrder( SanitizeHelper::apply( $_GET['id'] ) );
		return wp_send_json(
			$results,
			200
		);
	}

	/**
	 * Function to perform the quotation on the product calculator
	 *
	 * @return json
	 */
	public function cotationProductPage() {
		$data = SanitizeHelper::apply( $_POST['data'] );

		$this->isValidRequest( $data );

		$rates = ( new QuotationProductPageService(
			intval( $data['id_produto'] ),
			$data['cep_origem'],
			$data['quantity']
		) )->getRatesShipping();

		if ( ! empty( $rates['error'] ) ) {
			return wp_send_json(
				array(
					'success' => false,
					'error'   => $rates['error'],
				),
				500
			);
		}

		return wp_send_json(
			array(
				'success' => true,
				'data'    => $rates,
			),
			200
		);
	}

	/**
	 * Function to validate request in screen product
	 *
	 * @param array $data
	 * @return json
	 */
	private function isValidRequest( $data ) {
		if ( ! isset( $data['cep_origem'] ) ) {
			return wp_send_json(
				array(
					'success' => false,
					'message' => 'Infomar CEP de origem',
				),
				400
			);
		}
	}

	/**
	 * @param [type] $package
	 * @param [type] $services
	 * @param [type] $to
	 * @param array  $options
	 * @return void
	 */
	public function makeCotationPackage( $package, $services, $to, $options = array() ) {
		return $this->makeCotation( $to, $services, array(), $package, $options, false );
	}
}

$quotationController = new QuotationController();

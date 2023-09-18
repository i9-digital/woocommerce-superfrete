<?php

namespace Superfrete\Services;

use Superfrete\Models\Option;
use Superfrete\Models\Payload;
use Superfrete\Helpers\TimeHelper;
use Superfrete\Helpers\SessionHelper;
use Superfrete\Services\PayloadService;
use Superfrete\Services\WooCommerceBundleProductsService;

/**
 * Class responsible for the quotation service with the SuperFrete api.
 */
class QuotationService {

	const SUPERFRETE_ROUTE_CALCULATE = SUPERFRETE_CONFIG_ROUTE_CALCULATE;

	const SUPERFRETE_TIME_DURATION_SESSION_QUOTATION_IN_SECONDS = SUPERFRETE_CONFIG_TIME_DURATION_SESSION_QUOTATION_IN_SECONDS;

	/**
	 * function to calculate quotation.
	 *
	 * @param object $body
	 * @param bool   $useInsuranceValue
	 * @return array
	 */
	public function calculate( $payload, $useInsuranceValue ) {
		if ( empty( $payload ) ) {
			return false;
		}

		if (function_exists( 'write_log' ) ) {
			write_log('- - - Calculate Function() -> Use Insurance Value! - - - ');
			write_log(print_r($useInsuranceValue, true));
			write_log('- - -');
		}

		$requestService = new RequestService();

		$quotations = $requestService->request(
			self::SUPERFRETE_ROUTE_CALCULATE,
			'POST',
			$payload,
			true
		);

		if ( ! $useInsuranceValue ) {
			if (function_exists( 'write_log' ) ) {
				write_log('- - - Dont Use Insurance Value! - - - ');
			}
	
			$payload           = ( new PayloadService() )->removeInsuranceValue( $payload );
			$quotsWithoutValue = $requestService->request(
				self::SUPERFRETE_ROUTE_CALCULATE,
				'POST',
				$payload,
				true
			);
			if ( is_array( $quotations ) && is_array( $quotsWithoutValue ) ) {
				$quotations = array_merge( $quotations, $quotsWithoutValue );
				$quotations = $this->setKeyQuotationAsServiceid( $quotations );
			}
		} else {
			if (function_exists( 'write_log' ) ) {
				write_log('- - - Use Insurance Value! - - - ');
			}
		}

		return $quotations;
	}

	/**
	 * function to set each key of array as service id
	 *
	 * @param array $quotations
	 * @return array
	 */
	private function setKeyQuotationAsServiceid( $quotations ) {
		$response = array();

		foreach ( $quotations as $quotation ) {
			if ( isset( $quotation->id ) ) {
				$response[ $quotation->id ] = $quotation;
			}
		}
		return $response;
	}

	/**
	 * Function to calculate a quotation by post_id.
	 *
	 * @param int $postId
	 * @return array $quotation
	 */
	public function calculateQuotationByPostId( $postId ) {
		$products = ( new OrdersProductsService() )->getProductsOrder( $postId );
		$buyer    = ( new BuyerService() )->getDataBuyerByOrderId( $postId );
		$payload  = ( new PayloadService() )->createPayloadByProducts(
			$buyer->postal_code,
			$products
		);

		if ( ! ( new PayloadService() )->validatePayload( $payload ) ) {
			return false;
		}

		if (function_exists( 'write_log' ) ) {
			write_log('- - - Payload Options - LINE 95 - - - ');
			write_log(print_r($payload->options, true));
			write_log('- - - Payload Options Use_Insurance_Value - LINE 97 - - - ');
			write_log(print_r($payload->options->use_insurance_value, true));
		}
		
		$quotations = $this->calculate(
			$payload,
			( isset( $payload->options->use_insurance_value ) )
				? $payload->options->use_insurance_value
				: false
		);

		/* ///@Melhoria na Validação */
		/* Error Simulate */
		/*
		$quotations = (object) array(
			'success' => false,
			'errors'  => array(
				'package.height' => 'The package.height can not be greater than 150 cm.',
				'package.width' => 'The package.width can not be greater than 200 cm.'
			),
			'message' => 'One or more errors ocurred.'
		);
		*/

		/*
		$quotations = (object) array(
			"message" => "Invalid token",
			"error" => "Invalid token"
		);
		*/

		if ( !empty($quotations->errors) || !empty($quotations->error) ) {
			if(!empty($quotations->error)) {
				$quotations->errors = array(
					'correios' => 'Estamos enfrentando instabilidades em nossos serviços, por favor tente novamente mais tarde, ou entre em contato com nosso suporte.'
				);
			}

			return (object) array(
				'success' => false,
				'errors'  => $quotations->errors
			);
		}
		/* */
		
		$quotations = $this->removeItemNotHasPrice( $quotations );

		return ( new OrderQuotationService() )->saveQuotation( $postId, $quotations );
	}


	/**
	 * Function to remove quotes without price, that is, unavailable
	 *
	 * @param array $quotations
	 * @return array
	 */
	private function removeItemNotHasPrice( $quotations ) {
		foreach ( $quotations as $key => $quotation ) {
			if ( empty( $quotation->price ) ) {
				unset( $quotations[ $key ] );
			}
		}

		return $quotations;
	}

	/**
	 * Function to calculate a quotation by products.
	 *
	 * @param array  $products
	 * @param string $postalCode
	 * @param int    $service
	 * @return array|false|object
	 */
	public function calculateQuotationByProducts(
		$products,
		$postalCode,
		$service = null
	) {

		SessionHelper::initIfNotExists();

		$payload = ( new PayloadService() )->createPayloadByProducts(
			$postalCode,
			$products
		);
		if ( empty( $payload ) ) {
			return false;
		}

		$hash = $this->generateHashQuotation( $payload );

		$options = ( new Option() )->getOptions();

		if (function_exists( 'write_log' ) ) {
			write_log('- - - Options - LINE 193 - - - ');
			write_log(print_r($options, true));
			write_log('- - - Options Use_Insurance_Value - LINE 195 - - - ');
			write_log(print_r($payload->options->use_insurance_value, true));
		}

		$cachedQuotations = $this->getSessionCachedQuotations( $hash );

		if ( empty( $cachedQuotations ) ) {
			$quotations = $this->calculate( $payload, $options->insurance_value );

			/* ///@Melhorias */
			if(!empty($quotations->errors)) {
				return $quotations;
			}	
			/* */
			
			$quotations = $this->removeItemNotHasPrice( $quotations );

			$this->storeQuotationSession( $hash, $quotations );
			return $quotations;
		}

		if ( ! empty( $cachedQuotations ) && empty( $service ) ) {
			return $cachedQuotations;
		}

		if ( ! empty( $cachedQuotations ) && ! empty( $service ) ) {
			$cachedQuotation  = null;
			$cachedQuotations = $this->setKeyQuotationAsServiceid( $cachedQuotations );
			foreach ( $cachedQuotations as $quotation ) {
				if ( isset( $quotation->id ) && $quotation->id == $service ) {
					$cachedQuotation = $quotation;
				}
			}

			if ( ! empty( $cachedQuotation ) ) {
				return $cachedQuotation;
			}
		}

		return $cachedQuotations;
	}


	/**
	 * Function to save response quotation on session.
	 *
	 * @param array $bodyQuotation
	 * @param array $quotation
	 * @return void
	 */
	private function storeQuotationSession( $hash, $quotation ) {
		$quotationSession[ $hash ]['quotations'] = $quotation;
		$quotationSession[ $hash ]['created']    = date( 'Y-m-d H:i:s' );

		$_SESSION['quotation-superfrete'][ $hash ] = array(
			'quotations' => $quotation,
			'created'    => date( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Function to search for the quotation of a shipping service in the session,
	 * if it does not find false returns
	 *
	 * @param array $bodyQuotation
	 * @return bool|array
	 */
	private function getSessionCachedQuotations( $hash ) {
		SessionHelper::initIfNotExists();

		@$session = $_SESSION;

		if ( empty( $session['quotation-superfrete'][ $hash ] ) ) {
			return false;
		}

		$cachedQuotation = $session['quotation-superfrete'][ $hash ];
		$dateCreated     = $cachedQuotation['created'];
		$cachedQuotation = $cachedQuotation['quotations'];

		if ( ! empty( $dateCreated ) ) {
			if ( $this->isOutdatedQuotation( $dateCreated ) ) {
				unset( $session['quotation-superfrete'][ $hash ] );
				$_SESSION = $session;
			}
		}

		return $cachedQuotation;
	}

	private function isOutdatedQuotation( $dateQuotation ) {
		return TimeHelper::getDiffFromNowInSeconds( $dateQuotation ) > self::SUPERFRETE_TIME_DURATION_SESSION_QUOTATION_IN_SECONDS;
	}

	/**
	 * function to created a hash by quotation.
	 *
	 * @param object $payload
	 * @return string
	 */
	private function generateHashQuotation( $payload ) {
		$products = array();

		if ( ! empty( $payload->products ) ) {
			foreach ( $payload->products as $product ) {
				$products[] = array(
					'id'            => $product->id,
					'width'         => $product->width,
					'height'        => $product->height,
					'length'        => $product->length,
					'weight'        => $product->weight,
					'unitary_value' => $product->unitary_value,
					'quantity'      => $product->quantity,
				);
			}
		}

		return hash(
			'sha512',
			json_encode(
				array(
					'from'     => $payload->from->postal_code,
					'to'       => $payload->to->postal_code,
					'options'  => array(
						'own_hand' => $payload->options->own_hand,
						'receipt'  => $payload->options->receipt,
					),
					'products' => $products,
				)
			)
		);
	}
}

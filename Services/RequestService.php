<?php

namespace Superfrete\Services;

use Superfrete\Services\ManageRequestService;
use Superfrete\Services\ClearDataStored;
use Superfrete\Models\Version;
use Superfrete\Models\ResponseStatus;
use Superfrete\Services\SessionNoticeService;

class RequestService {

	const URL = SUPERFRETE_CONFIG_URL;

	const SANDBOX_URL = SUPERFRETE_CONFIG_SANDBOX_URL;

	const TIMEOUT = SUPERFRETE_CONFIG_TIMEOUT;

	const WP_ERROR = 'WP_Error';

	protected $token;

	protected $headers;

	protected $url;

	public function __construct() {
		$tokenData = ( new TokenService() )->get();

		if ( ! $tokenData ) {
			return wp_send_json(
				array(
					'message' => 'Usuário não autorizado, verificar token do SuperFrete',
				),
				ResponseStatus::HTTP_UNAUTHORIZED
			);
		}

		if ( $tokenData['token_environment'] == 'production' ) {
			$this->token = $tokenData['token'];
			$this->url   = self::URL;
		} else {
			$this->token = $tokenData['token_sandbox'];
			$this->url   = self::SANDBOX_URL;
		}

		$this->headers = array(
			'Content-Type'      => 'application/json',
			'Accept'            => 'application/json',
			'Authorization'     => 'Bearer ' . $this->token,
			'platform'					=> SUPERFRETE_CONFIG_PLATFORM,
		);
	}

	/**
	 * Function to make a request to API SuperFrete.
	 *
	 * @param string $route
	 * @param string $typeRequest
	 * @param array  $body
	 * @return object $response
	 */
	public function request( $route, $typeRequest, $body, $useJson = true ) {
		if ( $useJson ) {
			$body = json_encode( $body );
		}

		$params = array(
			'headers'  => $this->headers,
			'method'   => $typeRequest,
			'body'     => $body,
			'timeout ' => self::TIMEOUT,
		);

		if ( ! ini_get( 'safe_mode' ) ){
			set_time_limit( self::TIMEOUT );
		}

		//@INJECT LOG
		if (function_exists( 'write_log' ) ) {
			write_log('- - -  ROUTE - - - ' . $route);
		}

		$responseRemote = wp_remote_post( $this->url . $route, $params );

		//@INJECT LOG
		if (function_exists( 'write_log' ) ) {
			write_log('- - -  REQUEST - - - ');
			write_log('REQUEST_URL: ' . $this->url . $route);
			write_log('REQUEST_METHOD: ' . $typeRequest);
			write_log('REQUEST_BODY: ' . print_r($body, true));
		}
		
		if ( ! is_array( $responseRemote ) ) {
			
			if ( get_class( $responseRemote ) === self::WP_ERROR ) {
				
				$msgWP_ERROR = $responseRemote->get_error_message();
				//@INJECT LOG
				if (function_exists( 'write_log' ) ) {
					write_log('- - - WP_ERROR - - - ');
					write_log($msgWP_ERROR);
					write_log('- - - END REQUEST - - - ');
				}
				
				if(strstr($msgWP_ERROR, 'cURL error 28') || strstr($msgWP_ERROR, 'timed out after')) { //IF TIMEOUT ERROR

					$paramsArr = json_decode($params['body'], true);

					if(strstr($route, '/cart')) {
						return (object) array(
							'success' => false,
							'errors'  => array( 'Estamos enfrentando algumas instabilidades devido a alta demanda, tente novamente...' ),
						);
					}
				
					if(strstr($route, '/checkout')) {

						$myParams = array(
							'headers'  => $this->headers,
							'method'   => 'GET',
							'body'     => '',
							'timeout ' => self::TIMEOUT,
						);
						$routeOrderGetInfo = SUPERFRETE_CONFIG_ROUTE_SEARCH . $paramsArr['orders'][0]; 
						$responseRemote = wp_remote_post( $this->url . $routeOrderGetInfo, $myParams );
						$response = json_decode(wp_remote_retrieve_body( $responseRemote ));

						/* validateResponse */
						$responseCode = ( ! empty( $responseRemote['response']['code'] ) )
							? $responseRemote['response']['code']
							: null;

						if ( $responseCode == ResponseStatus::HTTP_UNAUTHORIZED ) {
							( new SessionNoticeService() )->add(
								SessionNoticeService::NOTICE_INVALID_TOKEN,
								SessionNoticeService::NOTICE_INFO
							);
							( new ClearDataStored() )->clear();
						}

						if ( $responseCode != ResponseStatus::HTTP_OK ) {
							( new ClearDataStored() )->clear();
						}

						if ( empty( $response ) ) {
							( new ClearDataStored() )->clear();
							return (object) array(
								'success' => false,
								'errors'  => array( 'Ocorreu um erro ao se conectar com a API do SuperFrete' ),
							);
						}

						if ( ! empty( $response->message ) && $response->message == 'Unauthenticated.' ) {
							return (object) array(
								'success' => false,
								'errors'  => array( 'Usuário não autenticado' ),
							);
						}

						$errors = $this->treatmentErrors( $response );

						if ( ! empty( $errors ) ) {
							return (object) array(
								'success' => false,
								'errors'  => $errors,
							);
						}
						/* */

						$myReturn = (object) array(
							'success' => true,
							'purchase' => (object) array(
								'id'	=> $response->id,
								'status'	=> $response->status,	
								'orders' => array(
										(object) array( 
											'id'						=> $response->id,
											'protocol'			=> $response->protocol,
											'service_id'		=> $response->service_id,
											'price'					=> $response->price,
											'discount'			=> $response->discount,
											'self_tracking' => $response->tracking,
											'tracking'    	=> $response->tracking,
											'print'					=> $response->print->url
									)
								)
							)
						);

						return $myReturn;
			
					}
				}

				return (object) array();
			}
		}

		//@INJECT LOG
		if (function_exists( 'write_log' ) ) {
			write_log('RESPONSE_BODY: ' . wp_remote_retrieve_body( $responseRemote ));
			write_log('- - - END REQUEST - - - ');
		}

		$response = json_decode(
			wp_remote_retrieve_body( $responseRemote )
		);

						/* validateResponse */
						$responseCode = ( ! empty( $responseRemote['response']['code'] ) )
							? $responseRemote['response']['code']
							: null;

						if ( $responseCode == ResponseStatus::HTTP_UNAUTHORIZED ) {
							( new SessionNoticeService() )->add(
								SessionNoticeService::NOTICE_INVALID_TOKEN,
								SessionNoticeService::NOTICE_INFO
							);
							( new ClearDataStored() )->clear();
						}

						if ( $responseCode != ResponseStatus::HTTP_OK ) {
							( new ClearDataStored() )->clear();
						}

						if ( empty( $response ) ) {
							( new ClearDataStored() )->clear();
							return (object) array(
								'success' => false,
								'errors'  => array( 'Ocorreu um erro ao se conectar com a API do SuperFrete' ),
							);
		}

		if ( ! empty( $response->message ) && $response->message == 'Unauthenticated.' ) {
			return (object) array(
				'success' => false,
				'errors'  => array( 'Usuário não autenticado' ),
			);
		}

		$errors = $this->treatmentErrors( $response );

		if ( ! empty( $errors ) ) {
			return (object) array(
				'success' => false,
				'errors'  => $errors,
			);
		}
		/* */

		return $response;

	}

	
	/**
	 * treatment errors to user
	 *
	 * @param object $data
	 * @return array $errors
	 */
	private function treatmentErrors( $data ) {
		$errorsResponse = array();
		$errors         = array();

		if ( ! empty( $data->error ) ) {
			$errors[] = $data->error;
		}

		if ( ! empty( $data->errors ) ) {
			foreach ( $data->errors as $errors ) {
				$errorsResponse[] = $errors;
			}
		}

		if ( ! empty( $errorsResponse ) && is_array( $errorsResponse ) ) {
			foreach ( $errorsResponse as $error ) {
				$errors[] = end( $error );
			}
		}

		return $errors;
	}
}

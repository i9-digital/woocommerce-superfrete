<?php

namespace IntegrationAPI\Services;

use IntegrationAPI\Models\Order;
use IntegrationAPI\Models\Method;
use IntegrationAPI\Models\ShippingService;

class OrderService {

	const REASON_CANCELED_USER = CONFIG_REASON_CANCELED_USER;

	const ROUTE_INTEGRATION_API_CANCEL = CONFIG_ROUTE_INTEGRATION_API_CANCEL;
	const ROUTE_INTEGRATION_API_CANCELLABLE = CONFIG_ROUTE_INTEGRATION_API_CANCELLABLE;
	const ROUTE_INTEGRATION_API_TRACKING = CONFIG_ROUTE_INTEGRATION_API_TRACKING;
	const ROUTE_INTEGRATION_API_CART = CONFIG_ROUTE_INTEGRATION_API_CART;
	const ROUTE_INTEGRATION_API_CHECKOUT = CONFIG_ROUTE_INTEGRATION_API_CHECKOUT;
	const ROUTE_INTEGRATION_API_CREATE_LABEL = CONFIG_ROUTE_INTEGRATION_API_CREATE_LABEL;
	const ROUTE_INTEGRATION_API_PRINT_LABEL = CONFIG_ROUTE_INTEGRATION_API_PRINT_LABEL;
	const ROUTE_INTEGRATION_API_SEARCH = CONFIG_ROUTE_INTEGRATION_API_SEARCH;

	const DEFAULT_METHOD_ID = CONFIG_DEFAULT_METHOD_ID;

	/**
	 * Function to cancel order on api SuperFrete.
	 *
	 * @param int $postId
	 * @return array $response
	 */
	public function cancel( $postId ) {
		$orderId = $this->getOrderIdByPostId( $postId );

		if ( is_null( $orderId ) ) {
			return array(
				'success' => false,
				'message' => 'Pedido não encontrado',
			);
		}

		$body['order'] = array(
			'id'          => $orderId,
			'reason_id'   => self::REASON_CANCELED_USER,
			'description' => 'Cancelado pelo usuário',
		);

		( new OrderQuotationService() )->removeDataQuotation( $postId );

		return ( new RequestService() )->request(
			self::ROUTE_INTEGRATION_API_CANCEL,
			'POST',
			json_encode( $body ),
			false
		);
	}

	/**
	 * Function to get info about order in api SuperFrete.
	 *
	 * @param int $orderId
	 * @return array $response
	 */
	public function info( $postId ) {
		$data = ( new OrderQuotationService() )->getData( $postId );

		if ( ! $data ) {
			return array(
				'success' => false,
				'message' => 'Ordem não encontrada no SuperFrete',
			);
		}

		return ( new RequestService() )->request(
			self::ROUTE_INTEGRATION_API_CART . '/' . $data['order_id'],
			'GET',
			array(),
			false
		);
	}


	/**
	 * Function to get details about order in api SuperFrete.
	 *
	 * @param int $postId
	 * @return array $response
	 */
	public function detail( $postId ) {
		$data = ( new OrderQuotationService() )->getData( $postId );

		if ( empty( $data['order_id'] ) ) {
			return array(
				'success' => false,
				'message' => 'Pedido não possui etiqueta do SuperFrete',
			);
		}

		$body = array(
			'orders' => (array) $data['order_id'],
		);

		if ( ! $data ) {
			return array(
				'success' => false,
				'message' => 'Ordem não encontrada no SuperFrete',
			);
		}

		return ( new RequestService() )->request(
			self::ROUTE_INTEGRATION_API_TRACKING,
			'POST',
			$body,
			true
		);
	}

	/**
	 * Function to create a label on SuperFrete.
	 *
	 * @param array $postsId
	 * @return array $response
	 */
	public function pay( $postsId ) {
		$wallet = 0;
		$orders = array();

		foreach ( $postsId as $postId ) {
			$orderId = $this->getOrderIdByPostId( $postId );

			if ( is_null( $orderId ) ) {
				continue;
			}

			$orders[] = $orderId;

			if (function_exists( 'write_log' ) ) {
				write_log('- - -  REQUEST DATA ORDER_ID - - - ');
				write_log(print_r($orderID, true));
			}	

			$ticket   = $this->infoOrderCart( $orderId );
			
			if (function_exists( 'write_log' ) ) {
				write_log('- - -  REQUEST DATA infoOrderCart - - - ');
				write_log(print_r($ticket, true));
			}				
			
			$wallet   = $wallet + $ticket->price;
		}

		if ( $wallet == 0 ) {
			return array(
				'success' => false,
				'message' => 'Sem pedidos para pagar',
			);
		}

		$body = array(
			'orders' => $orders,
			'wallet' => $wallet,
		);

		$result = ( new RequestService() )->request(
			self::ROUTE_INTEGRATION_API_CHECKOUT,
			'POST',
			$body,
			true
		);

		if ( array_key_exists( 'errors', $result ) ) {
			return $result;
		} 

		return ( new OrderQuotationService() )->updateDataQuotation(
			end( $postsId ),
			end( $result->purchase->orders )->id,
			end( $result->purchase->orders )->protocol,
			$result->purchase->status,
			end( $result->purchase->orders )->service_id,
			$result->purchase->id,
			end( $result->purchase->orders )->self_tracking
		);
	}

	/**
	 * Function to create a label on SuperFrete.
	 *
	 * @param array   $postId
	 * @param $orderId
	 * @return array $response
	 */
	public function payByOrderId( $postId, $orderId ) {
		$wallet = 0;
		$orders = array();

		$orders[] = $orderId;
		$ticket   = $this->infoOrderCart( $orderId );
		$wallet   = $wallet + $ticket->price;

		if ( $wallet == 0 ) {
			return array(
				'success' => false,
				'errors'  => (array) 'Sem pedidos para pagar',
			);
		}

		$body = array(
			'orders' => $orders,
			'wallet' => $wallet,
		);

		$result = ( new RequestService() )->request(
			self::ROUTE_INTEGRATION_API_CHECKOUT,
			'POST',
			$body,
			true
		);	

		if ( ! empty( $result->errors ) ) {
			return array(
				'success' => false,
				'errors'  => $result->errors,
			);
		}

		$response = ( new OrderQuotationService() )->updateDataQuotation(
			$postId,
			end( $result->purchase->orders )->id,
			end( $result->purchase->orders )->protocol,
			$result->purchase->status,
			end( $result->purchase->orders )->service_id,
			$result->purchase->id,
			end( $result->purchase->orders )->self_tracking
		);

		$response['result'] = $result;

		//@INJECT LOG
		if (function_exists( 'write_log' ) ) {
			write_log('- - -  RESULT RESPONSE AFTER CLICK PAY - - - ');
			write_log(print_r($response, true));
		}		

		return $response;
	}
	/**
	 * Function to create a label printble on integration api.
	 *
	 * @param int $postId
	 * @return void
	 */
	public function createLabel( $postId ) {
		$orderId = $this->getOrderIdByPostId( $postId );

		$body = array(
			'orders' => (array) $orderId,
			'mode'   => 'public',
		);

		$data = ( new OrderQuotationService() )->getData( $postId );

		if ( $data['status'] == Order::STATUS_GENERATED ) {
			return $data;
		}

		$result = ( new RequestService() )->request(
			self::ROUTE_INTEGRATION_API_CREATE_LABEL,
			'POST',
			$body,
			true
		);

		if ( ! empty( $result->errors ) ) {
			return array(
				'success' => false,
				'errors'  => $result->errors,
			);
		}

		$data = ( new OrderQuotationService() )->getData( $postId );

		$data = ( new OrderQuotationService() )->updateDataQuotation(
			$postId,
			$data['order_id'],
			$data['protocol'],
			Order::STATUS_GENERATED,
			$data['choose_method'],
			$data['purchase_id'],
			$data['tracking']
		);

		return $data;
	}

	public function printLabel( $postId ) {
		$orderId = $this->getOrderIdByPostId( $postId );

		$body = array(
			'orders' => (array) $orderId,
		);

		if (function_exists( 'write_log' ) ) {
			write_log('- - - printLabel - BODY - - - ');
			write_log(print_r($body, true));
		}

		$result = ( new RequestService() )->request(
			self::ROUTE_INTEGRATION_API_PRINT_LABEL,
			'POST',
			$body,
			true
		);

		if (function_exists( 'write_log' ) ) {
			write_log('- - - printLabel - result - - - ');
			write_log(print_r($result, true));
		}		

		if ( ! isset( $result->url ) ) {
			return array(
				'success' => false,
				'message' => 'Não foi possível imprimir a etiqueta',
			);
		}

		$data = ( new OrderQuotationService() )->getData( $postId );

		$data = ( new OrderQuotationService() )->updateDataQuotation(
			$postId,
			$data['order_id'],
			$data['protocol'],
			Order::STATUS_RELEASED,
			$data['choose_method'],
			$data['purchase_id'],
			$data['tracking']
		);

		return $result;
	}

	/**
	 * Function to get info about order in api SuperFrete.
	 *
	 * @param int $order_id
	 * @return array $response
	 */
	public function infoOrderCart( $orderId ) {
		return ( new RequestService() )->request(
			self::ROUTE_INTEGRATION_API_SEARCH . '/' . $orderId,
			'GET',
			array(),
			false
		);
	}

	/**
	 * Function to get information in SuperFrete.
	 *
	 * @param string $order_id
	 * @return array $response
	 */
	public function getInfoOrder( $orderId ) {
		return ( new RequestService() )->request(
			self::ROUTE_INTEGRATION_API_SEARCH . $orderId,
			'GET',
			array(),
			false
		);
	}

	/**
	 * Function to get order_id by post_id.
	 *
	 * @param int $postId
	 * @return string $order_id
	 */
	public function getOrderIdByPostId( $postId ) {
		$data = ( new OrderQuotationService() )->getData( $postId );

		if ( ! isset( $data['order_id'] ) ) {
			return null;
		}

		return $data['order_id'];
	}

	/**
	 * Function to merge status with stauts integration api.
	 *
	 * @param array $posts
	 * @return array $response
	 */
	public function mergeStatus( $posts ) {
		$response = array();

		foreach ( $posts as $post ) {
			$data = ( new OrderQuotationService() )->getData( $post->ID );


			if (function_exists( 'write_log' ) ) {
				write_log('- - - mergeStatus - ID - - - ' . $post->ID);
				write_log(print_r($data, true));
			}

			if ( empty( $data ) ) {
				$response[ $post->ID ] = array(
					'order_id'   => null,
					'status'     => null,
					'protocol'   => null,
					'tracking'   => null,
					'service_id' => null,
				);
				continue;
			}

			$dataOrder = $this->getInfoOrder( $data['order_id'] );

			if ( ! isset( $dataOrder->id ) ) {
				$response[ $post->ID ] = array(
					'order_id'   => null,
					'status'     => null,
					'protocol'   => null,
					'tracking'   => null,
					'service_id' => null,
				);
				continue;
			}

			$response[ $post->ID ] = array(
				'order_id'   => $data['order_id'],
				'status'     => $dataOrder->status,
				'protocol'   => $dataOrder->protocol,
				'tracking'   => $dataOrder->tracking,
				'service_id' => ( ! empty( $data['choose_method'] ) ) ? $data['choose_method'] : null,
			);

			if ( ! is_null( $dataOrder->tracking ) ) {
				( new TrackingService() )->addTrackingOrder(
					$post->ID,
					$dataOrder->tracking
				);
			}
		}

		return $response;
	}

	/**
	 * Multi-label purchase function in a single click
	 *
	 * @param array $posts
	 * @return array
	 */
	public function buyOnClick( $posts ) {
		$orders = array();

		$errors = array();

		foreach ( $posts as $postId ) {
			$data = ( new OrderQuotationService() )->getData( $postId );

			if ( empty( $data ) || is_null( $data['order_id'] ) ) {
				$products = ( new OrdersProductsService() )->getProductsOrder( $postId );

				$to = ( new BuyerService() )->getDataBuyerByOrderId( $postId );

				$chooseMethod = ( new Method() )->getMethodShipmentSelected( $postId );

				$data = ( new cartService() )->add( $postId, $products, $to, $chooseMethod );

				if ( isset( $data['message'] ) ) {
					$errors[ $postId ][] = $data['message'];
				}
			}

			if ( $data['status'] == Order::STATUS_PENDING ) {
				$data = $this->payByOrderId( $postId, $data['order_id'] );

				if ( isset( $data['message'] ) ) {
					$errors[ $postId ][] = $data['message'];
				}
			}

			if ( $data['status'] == Order::STATUS_PAID ) {

				$data = $this->createLabel( $postId );

				if ( isset( $data['message'] ) ) {
					$errors[ $postId ][] = $data['message'];
				}

				if ( isset( $data['message'] ) ) {
					$errors[ $postId ][] = $data['message'];
				}

				$orders[ $postId ] = $data['order_id'];
			}

			if ( $data['status'] == Order::STATUS_GENERATED || $data['status'] == Order::STATUS_RELEASED ) {
				if ( isset( $data['message'] ) ) {
					$errors[ $postId ][] = $data['message'];
				}

				$orders[ $postId ] = $data['order_id'];
			}
		}

		if ( ! empty( $orders ) ) {
			$body = array(
				'orders' => $orders,
			);

			$result = ( new RequestService() )->request(
				self::ROUTE_INTEGRATION_API_PRINT_LABEL,
				'POST',
				$body,
				true
			);
		}

		return array(
			'url'    => $result->url,
			'errors' => $errors,
		);
	}

	/**
	 * Function to get method_id selected by postId
	 *
	 * @param $postId
	 * @return int|bool
	 */
	public function getMethodIdSelected( $postId ) {
		$order = wc_get_order( $postId );
		$items = $order->get_items( 'shipping' );
		if ( empty( $items ) ) {
			return false;
		}
		$shipping_item_data = end( $items )->get_data();
		$method_id          = ( empty( $shipping_item_data['method_id'] ) )
			? self::DEFAULT_METHOD_ID
			: $shipping_item_data['method_id'];

		return ShippingService::getCodeByMethodId( $method_id );
	}
}

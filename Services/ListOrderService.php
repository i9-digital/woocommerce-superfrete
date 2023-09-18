<?php

namespace Superfrete\Services;

use Superfrete\Models\ShippingService;
use Superfrete\Helpers\TranslateStatusHelper;
use Superfrete\Helpers\ProductVirtualHelper;
use Superfrete\Models\User;

class ListOrderService {

	/**
	 * Function to return the list of orders
	 *
	 * @param array $args
	 * @return array
	 */
	public function getList( $args ) {
		$limit    = $args['limit'];
		$skip     = $args['skip'];
		$status   = $args['status'];
		$wpstatus = $args['wpstatus'];

		$posts = $this->getPosts( $limit, $skip, $status, $wpstatus );

		if ( empty( $posts ) ) {
			return array(
				'orders' => array(),
				'load'   => false,
			);
		}

		$orders = $this->getData( $posts );

		return array(
			'orders' => $orders,
			'load'   => ( count( $orders ) == ( $limit ) ?: 5 ) ? true : false,
		);
	}

	/**
	 * Function to return the list of orders with the data of the SuperFrete
	 *
	 * @param array $posts
	 * @return array
	 */
	private function getData( $posts ) {
		$orders = array();

		$statusSuperfrete = ( new OrderService() )->mergeStatus( $posts );

		$quotationService = new QuotationService();

		$buyerService = new BuyerService();

		$translateHelper = new TranslateStatusHelper();

		$productService = new OrdersProductsService();

		$userData = ( new User() )->get();
		//@INJECT LOG
		if (function_exists( 'write_log' ) ) {
			write_log('- - -  REQUEST DATA getMe - - - ');
			write_log(print_r($userData, true));
			write_log('---');
			write_log(print_r($userData['data']['id'], true));
		}
		$userDataUID = $userData['data']['id'];
				
		foreach ( $posts as $post ) {
			$postId = $post->ID;

			$invoice = ( new InvoiceService() )->getInvoice( $postId );

			$products = $productService->getProductsOrder( $postId );

			$products = ProductVirtualHelper::removeVirtuals( $products );

			/* ESCONDE METODOS DIFERENTES DO SUPORTADO PELO SUPERFRETE */
			//$testMethodId = ( new OrderService() )->getMethodIdSelected( $postId );
			//if(is_null($testMethodId)) continue;
			/**/

			$orders[] = array(
				'id'             => $postId,
				'tracking'       => $statusSuperfrete[ $postId ]['tracking'],
				'link_tracking'  => ( ! is_null( $statusSuperfrete[ $postId ]['tracking'] ) )
					? sprintf( 'https://rastreio.superfrete.com/#/tracking/%s', md5($userDataUID . $statusSuperfrete[ $postId ]['tracking']) )
					: null,
				'to'             => $buyerService->getDataBuyerByOrderId( $postId ),
				'status'         => $statusSuperfrete[ $postId ]['status'],
				'status_texto'   => $translateHelper->translateNameStatus(
					$statusSuperfrete[ $postId ]['status']
				),
				'order_id'       => $statusSuperfrete[ $postId ]['order_id'],
				'service_id'     => ( ! empty( $statusSuperfrete[ $postId ]['service_id'] ) )
					? $statusSuperfrete[ $postId ]['service_id']
					: ShippingService::CORREIOS_SEDEX,
				'protocol'       => $statusSuperfrete[ $postId ]['protocol'],
				'non_commercial' => !$invoice['number'] || !$invoice['key'],
				'invoice'        => $invoice,
				'products'       => $products,
				'quotation'      => $quotationService->calculateQuotationByPostId( $postId ),
				'link'           => admin_url() . sprintf( 'post.php?post=%d&action=edit', $postId ),
			);
		}

		return $orders;
	}

	/**
	 * Function to get posts
	 *
	 * @param int    $limit
	 * @param int    $skip
	 * @param string $status
	 * @param string $wpstatus
	 * @return array posts
	 */
	private function getPosts( $limit, $skip, $status, $wpstatus ) {
		$args = array(
			'numberposts' => ( $limit ) ?: 5,
			'offset'      => ( $skip ) ?: 0,
			'post_type'   => 'shop_order',
		);

		if ( isset( $wpstatus ) && $wpstatus != 'all' ) {
			$args['post_status'] = $wpstatus;
		} elseif ( isset( $wpstatus ) && $wpstatus == 'all' ) {
			$args['post_status'] = array_keys( wc_get_order_statuses() );
		} else {
			$args['post_status'] = 'publish';
		}

		if ( isset( $status ) && $status != 'all' ) {
			$args['meta_query'] = array(
				array(
					'key'     => 'superfrete_status_v2',
					'value'   => sprintf( ':"%s";', $status ),
					'compare' => 'LIKE',
				),
			);
		}

		return get_posts( $args );
	}
}

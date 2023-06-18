<?php

namespace IntegrationAPI\Services;

use IntegrationAPI\Controllers\AgenciesController;
use IntegrationAPI\Controllers\ConfigurationController;
use IntegrationAPI\Controllers\LocationsController;
use IntegrationAPI\Controllers\OrdersController;
use IntegrationAPI\Controllers\QuotationController;
use IntegrationAPI\Controllers\SessionsController;
use IntegrationAPI\Controllers\StatusController;
use IntegrationAPI\Controllers\TokenController;
use IntegrationAPI\Controllers\UsersController;
use IntegrationAPI\Controllers\PathController;
use IntegrationAPI\Controllers\PayloadsController;
use IntegrationAPI\Controllers\CartController;
use IntegrationAPI\Controllers\NoticeFormController;
use IntegrationAPI\Helpers\SanitizeHelper;
use IntegrationAPI\Models\Version;

/**
 * Class responsible for managing the routes of the plugin
 */
class RouterService {


	const MESSAGE_ERROR_NOT_POST_ID = 'Informar o campo "post_id"';

	public function handler() {
		$this->loadRoutesOrders();
		$this->loadRoutesUsers();
		$this->loadRoutesQuotations();
		$this->loadRoutesConfigurations();
		$this->loadRoutesStatus();
		$this->loadRoutesTokens();
		$this->loadRoutesTest();
		$this->loadRoutesSession();
		$this->loadRoutesLocation();
		$this->loadRoutesPath();
		$this->laodRoutesPayload();
		$this->loadRoutesNotices();
		$this->loadRoutesTestUserWooCommerceData();
		$this->loadRouteDataUser();
		$this->loadRouteCart();
		$this->loadRouteForm();
		$this->loadRoutesAgencies();
	}

	/**
	 * function to start users routes
	 *
	 * @return void
	 */
	private function loadRoutesUsers() {
		$usersController = new UsersController();

		add_action( 'wp_ajax_sf_me', array( $usersController, 'getMe' ) );
		add_action( 'wp_ajax_get_sf_balance', array( $usersController, 'getBalance' ) );
	}

	/**
	 * function to start users routes
	 *
	 * @return void
	 */
	private function loadRoutesOrders() {
		$ordersController = new OrdersController();

		add_action(
			'wp_ajax_get_sf_quotation',
			function () use ( $ordersController ) {
				$ordersController->getOrderQuotationByOrderId( $_GET['id'] );
			}
		);
		add_action( 'wp_ajax_get_sf_orders', array( $ordersController, 'getOrders' ) );
		add_action( 'wp_ajax_add_sf_cart', array( $ordersController, 'addCart' ) );
		add_action( 'wp_ajax_add_sf_order', array( $ordersController, 'sendOrder' ) );
		add_action( 'wp_ajax_buy_sf_click', array( $ordersController, 'buyOnClick' ) );
		add_action( 'wp_ajax_remove_sf_order', array( $ordersController, 'removeOrder' ) );
		add_action( 'wp_ajax_cancel_sf_order', array( $ordersController, 'cancelOrder' ) );
		add_action( 'wp_ajax_pay_sf_ticket', array( $ordersController, 'payTicket' ) );
		add_action( 'wp_ajax_create_sf_ticket', array( $ordersController, 'createTicket' ) );
		add_action( 'wp_ajax_print_sf_ticket', array( $ordersController, 'printTicket' ) );
		add_action( 'wp_ajax_insert_sf_invoice_order', array( $ordersController, 'insertInvoiceOrder' ) );
	}

	/**
	 * function to start quotations routes
	 *
	 * @return void
	 */
	private function loadRoutesQuotations() {
		$quotationsController = new QuotationController();

		add_action( 'wp_ajax_nopriv_cotation_sf_product_page', array( $quotationsController, 'cotationProductPage' ) );
		add_action( 'wp_ajax_cotation_sf_product_page', array( $quotationsController, 'cotationProductPage' ) );
		add_action( 'wp_ajax_update_sf_order', array( $quotationsController, 'refreshCotation' ) );
	}

	/**
	 * function to start configurations routes
	 *
	 * @return void
	 */
	private function loadRoutesConfigurations() {
		$configurationsController = new ConfigurationController();
		add_action( 'wp_ajax_get_sf_configuracoes', array( $configurationsController, 'getConfigurations' ) );
		add_action( 'wp_ajax_get_sf_metodos', array( $configurationsController, 'getMethodsEnables' ) );
		add_action( 'wp_ajax_save_sf_configuracoes', array( $configurationsController, 'saveAll' ) );
	}

	/**
	 * function to start status routes
	 *
	 * @return void
	 */
	private function loadRoutesStatus() {
		$statusController = new StatusController();

		add_action( 'wp_ajax_get_sf_status_woocommerce', array( $statusController, 'getStatus' ) );
	}

	/**
	 * function to start tokens routes
	 *
	 * @return void
	 */
	private function loadRoutesTokens() {
		$tokensController = new TokenController();

		add_action( 'wp_ajax_get_sf_token', array( $tokensController, 'get' ) );
		add_action( 'wp_ajax_save_sf_token', array( $tokensController, 'save' ) );
		add_action( 'wp_ajax_verify_sf_token', array( $tokensController, 'verifyToken' ) );
	}

	/**
	 * function to start tests routes
	 *
	 * @return void
	 */
	private function loadRoutesTest() {
		$version = Version::VERSION;

		add_action(
			'wp_ajax_nopriv_sf_environment',
			function () use ( $version ) {
				( new TestService( $version ) )->run();
			}
		);

		add_action(
			'wp_ajax_sf_environment',
			function () use ( $version ) {
				( new TestService( $version ) )->run();
			}
		);
	}

	/**
	 * function to start session routes
	 *
	 * @return void
	 */
	private function loadRoutesSession() {
		$sessionsController = new SessionsController();

		add_action( 'wp_ajax_delete_integration_api_session', array( $sessionsController, 'deleteSession' ) );
		add_action( 'wp_ajax_get_integration_api_session', array( $sessionsController, 'getSession' ) );
	}

	/**
	 * function to start location routes
	 *
	 * @return void
	 */
	private function loadRoutesLocation() {
		$locationController = new LocationsController();

		foreach ( array( 'wp_ajax_get_sf_address', 'wp_ajax_nopriv_get_sf_address' ) as $action ) {
			add_action(
				$action,
				function () use ( $locationController ) {
					if ( empty( $_GET['postal_code'] ) ) {
						return wp_send_json(
							array(
								'error'   => true,
								'message' => self::MESSAGE_ERROR_NOT_POST_ID,
							),
							400
						);
					}
					return $locationController->getAddressByPostalCode( SanitizeHelper::apply( $_GET['postal_code'] ) );
				}
			);
		}
	}

	/**
	 * function to start path routes
	 *
	 * @return void
	 */
	private function loadRoutesPath() {
		$pathController = new PathController();

		add_action( 'wp_ajax_check_sf_path', array( $pathController, 'getPathPlugin' ) );
	}

	/**
	 * function to start payload routes
	 *
	 * @return void
	 */
	private function laodRoutesPayload() {
		$payloadsController = new PayloadsController();

		if ( empty( $_GET['post_id'] ) ) {
			return false;
		}

		$postId = SanitizeHelper::apply( $_GET['post_id'] );

		add_action(
			'wp_ajax_nopriv_get_sf_payload',
			function () use ( $payloadsController, $postId ) {
				if ( empty( $_GET['post_id'] ) ) {
					return wp_send_json(
						array(
							'error'   => true,
							'message' => self::MESSAGE_ERROR_NOT_POST_ID,
						),
						400
					);
				}
				return $payloadsController->show( $postId );
			}
		);

		add_action(
			'wp_ajax_get_sf_payload',
			function () use ( $payloadsController, $postId ) {
				if ( empty( $_GET['post_id'] ) ) {
					return wp_send_json(
						array(
							'error'   => true,
							'message' => self::MESSAGE_ERROR_NOT_POST_ID,
						),
						400
					);
				}
				return $payloadsController->showLogged( $postId );
			}
		);

		add_action(
			'wp_ajax_destroy_sf_payload',
			function () use ( $payloadsController, $postId ) {
				if ( empty( $_GET['post_id'] ) ) {
					return wp_send_json(
						array(
							'error'   => true,
							'message' => self::MESSAGE_ERROR_NOT_POST_ID,
						),
						400
					);
				}
				return $payloadsController->destroy( $postId );
			}
		);

		add_action(
			'wp_ajax_get_sf_payload_cart',
			function () use ( $payloadsController ) {
				if ( empty( $_GET['post_id'] ) ) {
					return wp_send_json(
						array(
							'error'   => true,
							'message' => self::MESSAGE_ERROR_NOT_POST_ID,
						),
						400
					);
				}

				if ( empty( $_GET['service'] ) ) {
					return wp_send_json(
						array(
							'error'   => true,
							'message' => 'Informar o campo "service"',
						),
						400
					);
				}

				return $payloadsController->showPayloadCart(
					SanitizeHelper::apply( $_GET['post_id'] ),
					SanitizeHelper::apply( $_GET['service'] )
				);
			}
		);
	}

	/*
	 * function to start path notices
	 *
	 * @return void
	 */
	public function loadRoutesNotices() {
		add_action(
			'wp_ajax_get_sf_notices',
			function () {
				( new SessionNoticeService() )->get();
			}
		);

		add_action(
			'wp_ajax_remove_sf_notices',
			function () {
				( new SessionNoticeService() )->remove( SanitizeHelper::apply( $_GET['id'] ) );
			}
		);
	}

	public function loadRoutesTestUserWooCommerceData() {
		$locationService = new LocationService();

		add_action(
			'wp_ajax_test_sf_user_woocommerce_data',
			function () use ( $locationService ) {

				if ( empty( $_GET['postcode'] ) ) {
					return wp_send_json(
						array(
							'message' => 'Informar o parametro "postcode"',
						)
					);
				}

				$address = $locationService->getAddressByPostalCode( SanitizeHelper::apply( $_GET['postcode'] ) );

				$userData = ( new UserWooCommerceDataService() )->set( $address, true );

				return wp_send_json( $userData );
			}
		);
	}

	/*
	 * function to start user data routes
	 *
	 * @return void
	 */
	public function loadRouteDataUser() {
		$usersController = new UsersController();

		add_action(
			'wp_ajax_user_sf_woocommerce_data',
			function () use ( $usersController ) {
				return wp_send_json(
					array(
						'data' => $usersController->getFrom(),
					)
				);
			}
		);
	}

	public function loadRouteCart() {
		$cartController = new CartController();

		add_action(
			'wp_ajax_show_sf_cart',
			function () use ( $cartController ) {
				return wp_send_json(
					array(
						'data' => $cartController->getInfoCart(),
					)
				);
			}
		);
	}

	/*
	 * function to start form routes
	 *
	 * @return void
	 */
	public function loadRouteForm() {
		$formController = new NoticeFormController();

		add_action(
			'wp_ajax_open_form_integration_api',
			function () use ( $formController ) {
				return wp_send_json( $formController->openForm() );
			}
		);

		add_action(
			'wp_ajax_show_form_integration_api',
			function () use ( $formController ) {
				return wp_send_json( $formController->showForm() );
			}
		);

		add_action(
			'wp_ajax_hide_form_integration_api',
			function () use ( $formController ) {
				return wp_send_json( $formController->hideForm() );
			}
		);
	}

	/*
	 * function to start agencies routes
	 *
	 * @return json
	 */
	public function loadRoutesAgencies() {
		$agenciesController = new AgenciesController();
		add_action(
			'wp_ajax_get_sf_agencies',
			function () use ( $agenciesController ) {
				return $agenciesController->get();
			}
		);
	}
}

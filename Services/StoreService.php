<?php

namespace IntegrationAPI\Services;

use IntegrationAPI\Helpers\SessionHelper;
use IntegrationAPI\Models\Session;

class StoreService {

	const URL = CONFIG_URL;

	const OPTION_STORES = 'integrationapi_stores';

	const OPTION_STORE_SELECTED = 'integrationapi_store_v2';

	const SESSION_STORES = 'integrationapi_stores';

	const SESSION_STORE_SELECTED = 'integrationapi_store_v2';

	const ROUTE_INTEGRATION_API_COMPANIES = CONFIG_ROUTE_INTEGRATION_API_COMPANIES;

	public $store = null;

	/**
	 * Function to get store selected by seller.
	 *
	 * @return bool|object
	 */
	public function getStoreSelected() {
		$stores = $this->getStores();

		if ( empty( $stores ) ) {
			return false;
		}

		$storesSelected = array_filter(
			$stores,
			function ( $store ) {
				return ! empty( $store->selected );
			}
		);

		$storeSelected = end( $storesSelected );

		if ( empty( $storeSelected->name ) ) {
			return false;
		}

		$storeSelected->address = $this->getAddressStore( $storeSelected );

		return $storeSelected;
	}

	/**
	 * Function to retrieve stores.
	 *
	 * @return bool|array
	 */
	public function getStores() {
		SessionHelper::initIfNotExists();

		$codeStore = hash( 'sha512', get_option( 'home' ) );

		unset( $_SESSION[ Session::ME_KEY ][ $codeStore ][ self::SESSION_STORES ] );
		if ( isset( $_SESSION[ Session::ME_KEY ][ $codeStore ][ self::SESSION_STORES ] ) ) {
			return $_SESSION[ Session::ME_KEY ][ $codeStore ][ self::SESSION_STORES ];
		}

		$response = ( new RequestService() )->request(
			self::ROUTE_INTEGRATION_API_COMPANIES,
			'GET',
			array(),
			false
		);

		$stores = array();

		if ( ! isset( $response->data ) ) {
			return false;
		}

		$stores = $response->data;

		$storeSelected = $this->getSelectedStoreId();

		$stores = array_map(
			function ( $store ) use ( $storeSelected ) {
				if ( $store->id == $storeSelected ) {
					$store->selected = true;
				}

				$store->address = $this->getAddressStore( $store );

				return $store;
			},
			$stores
		);

		$_SESSION[ Session::ME_KEY ][ $codeStore ][ self::OPTION_STORES ] = $stores;

		session_write_close();

		return $stores;
	}

	/**
	 * Function to get the address of a store
	 *
	 * @param object $store
	 * @return object
	 */
	public function getAddressStore( $store ) {
		$addresses = ( new RequestService() )->request(
			self::ROUTE_INTEGRATION_API_COMPANIES . '/' . $store->id . '',
			'GET',
			array(),
			false
		);

		if ( empty( $addresses->data ) ) {
			return array();
		}

		return $addresses->data;
	}

	/**
	 * Return ID of store selected by user
	 *
	 * @return null|string
	 */
	public function getSelectedStoreId() {
		return get_option( self::OPTION_STORE_SELECTED, true );
	}

	/**
	 * Function to save store selected on WordPress.
	 *
	 * @param string $idStoreSelected
	 * @return bool
	 */
	public function setStore( $idStoreSelected ) {
		delete_option( self::OPTION_STORE_SELECTED );
		return add_option( self::OPTION_STORE_SELECTED, $idStoreSelected );
	}
}

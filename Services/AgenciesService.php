<?php

namespace Superfrete\Services;

class AgenciesService {

	const SUPERFRETE_ROUTE_GET_AGENCIES = SUPERFRETE_CONFIG_ROUTE_GET_AGENCIES;

	protected $state = null;

	protected $city = null;

	protected $company = null;

	public function __construct( $data ) {
		if ( ! empty( $data['state'] ) ) {
			$this->state = $data['state'];
		}

		if ( ! empty( $data['company'] ) ) {
			$this->company = $data['company'];
		}
	}

	/**
	 * function to get  agencies on SuperFrete API.
	 *
	 * @param array $data
	 * @return array
	 */
	public function get() {
		$route = $this->getRoute();

		$agencies = ( new RequestService() )->request(
			$route,
			'GET',
			array(),
			false
		);

		if ( empty( $agencies ) ) {
			return (object) array(
				'success' => false,
				'message' => 'Ocorreu um erro ao obter agências',
			);
		}

		return $this->normalize( $agencies );
	}

	/**
	 * @return string $route
	 */
	private function getRoute() {
		$data['country'] = 'BR';

		if ( ! empty( $this->state ) ) {
			$data['state'] = $this->state;
		}

		if ( ! empty( $this->company ) ) {
			$data['company'] = $this->company;
		}

		$query = http_build_query( $data );

		return self::SUPERFRETE_ROUTE_GET_AGENCIES . '?' . $query;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private function normalize( $data ) {
		$agencies = array();
		foreach ( $data as $agency ) {
			$companyId = null;

			if ( isset( $agency->company_id ) ) {
				$companyId = $agency->company_id;
			}

			if ( isset( $agency->companies[0]->id ) ) {
				$companyId = $agency->companies[0]->id;
			}

			if ( empty( $companyId ) ) {
				continue;
			}

			$agencies[ $companyId ][] = $agency;
		}

		if ( ! empty( $this->company ) ) {
			if ( ! empty( $agencies[ $this->company ] ) ) {
				return $agencies[ $this->company ];
			}
		}

		return $agencies;
	}
}

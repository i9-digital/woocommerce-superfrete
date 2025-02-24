<?php

namespace Superfrete\Services;

/**
 * Health service class
 */
class CheckHealthService {

	public function init() {
		$this->hasShippingMethodsSuperfrete();
	}

	/**
	 * Function to display message to the user if he does not
	 * have selected shipping methods
	 *
	 * @return void
	 */
	public function hasShippingMethodsSuperfrete() {
		add_action(
			'woocommerce_superfrete_init',
			function () {
				$methods = ( new ShippingSuperfreteService() )
				->getMethodsActivedsSuperfrete();

				if ( count( $methods ) == 0 ) {
					$message = sprintf(
						'<div class="error">
                    <h2>Atenção usuário do Plugin SuperFrete</h2>
                        <p>%s</p>
                    </div>',
						'Por favor, verificar os métodos de envios do SuperFrete na tela de <a href="/wp-admin/admin.php?page=wc-settings&tab=shipping">configurações de áreas de entregas do WooCommerce</a> após a instalação da versão <b>2.8.0</b>. Devido a nova funcionalidade de classes de entrega, é necessário selecionar novamente os métodos de envios do SuperFrete.'
					);

					( new SessionNoticeService() )->add(
						$message,
						SessionNoticeService::NOTICE_INFO
					);
				}
			}
		);
	}

	/**
	 * Function to check if the plugin has all the necessary plugins to run.
	 *
	 * @param string $pathPlugins
	 * @return array
	 */
	public function checkPathPlugin( $pathPlugins ) {
		$errorsPath = array();
		if ( ! is_dir( $pathPlugins . '/woocommerce' ) ) {
			$errorsPath[] = 'Defina o path do diretório de plugins nas configurações do plugin do SuperFrete';
		}

		$errors          = array();
		$pluginsActiveds = apply_filters(
			'network_admin_active_plugins',
			get_option( 'active_plugins' )
		);

		if ( ! class_exists( 'WooCommerce' ) ) {
			$errors[] = 'Você precisa do plugin WooCommerce ativado no WordPress para utilizar o plugin do SuperFrete';
		}

		if ( ! in_array( 'woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php', $pluginsActiveds ) && ! is_multisite() ) {
			$errors[] = 'Você precisa do plugin <a target="_blank" href="https://br.wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/">WooCommerce checkout fields for Brazil</a> ativado no wordpress para utilizar o plugin do SuperFrete';
		}

		$sessionNoticeService = new SessionNoticeService();
		if ( ! empty( $errors ) ) {
			foreach ( $errors as $err ) {
				$sessionNoticeService->add( $err, SessionNoticeService::NOTICE_INFO );
			}
		}

		return array(
			'errors'     => $errors,
			'errorsPath' => $errorsPath,
		);
	}
}

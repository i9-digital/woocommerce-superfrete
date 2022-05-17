<?php

namespace Services;

use Helpers\DimensionsHelper;
use Models\Option;
use Models\ResponseStatus;

class TestService
{
    const DEFAULT_QUANTITY_PRODUCT = 1;

    protected $version;

    public function __construct($version)
    {
        $this->version = $version;
    }

    public function run()
    {
        if (empty($_GET['hash'])) {
            return wp_send_json([
                'message' => 'Acesso não autorizado'
            ], ResponseStatus::HTTP_UNAUTHORIZED);
        }

        if (hash('sha512', $_GET['hash']) != '22b0e1b5ac96f76652c82b13bb01e3c9') {
            return wp_send_json([
                'message' => 'Acesso não autorizado'
            ], ResponseStatus::HTTP_UNAUTHORIZED);
        }

        $response = [
            'version' => $this->version,
            'php' => phpversion(),
            'environment' => (new TokenService())->check(),
            'user' => $this->hideDataMe((new SellerService())->getData()),
            'metrics' => $this->getMetrics(),
            'path' => $this->getPluginsPath(),
            'options' => (new Option())->getOptions(),
            'plugins' => $this->getInstalledPlugins(),
            'shipping-methods' => $this->getShippingMethods()
        ];

        if (isset($_GET['postalcode'])) {
            $productTest = $this->getProductToTest();

            if (!empty($productTest)) {
                $quotation = (new QuotationService())->calculateQuotationByProducts(
                    $productTest,
                    $_GET['postalcode'],
                    null
                );

                $response['product'] = $productTest;

                foreach ($quotation as $item) {
                    $packages = [];
                    if (!empty($item->packages)) {
                        foreach ($item->packages as $package) {
                            $packages[] = [
                                'largura' => $package->dimensions->width,
                                'altura' => $package->dimensions->height,
                                'comprimento' => $package->dimensions->length,
                                'peso' => $package->weight
                            ];
                        }
                    }

                    $response['quotation'][$item->id] = [
                        'Serviço' => $item->name,
                        'Valor' => isset($item->price) ? $item->price : null,
                        'Erro' => isset($item->error) ? $item->error : null,
                        'Entrega' => (isset($item->delivery_range))
                            ? sprintf(
                                '%d a %d dias',
                                $item->delivery_range->min,
                                $item->delivery_range->max
                            )
                            : null,
                        'Pacotes' => $packages
                    ];
                }
            }
        }

        return wp_send_json($response, ResponseStatus::HTTP_OK);
    }

    /**
     * Function to return path plugins.
     *
     * @return string
     */
    private function getPluginsPath()
    {
        $dir = dirname(__FILE__);
        $data = explode('/plugin-woocommerce', $dir);
        return $data[0];
    }

    /**
     * Function to get a list of plugins instaleds
     *
     * @return array $plugins
     */
    private function getInstalledPlugins()
    {
        return apply_filters(
            'network_admin_active_plugins',
            get_option('active_plugins')
        );
    }

    /**
     * Function to extract any data
     *
     * @param object $user
     * @return array $data
     */
    private function hideDataMe($data)
    {
        if (empty($data->email)) {
            return [
                'message' => 'Usuário não autenticado'
            ];
        }

        $dataEmail = explode('@', $data->email);

        $total = strlen($dataEmail[0]);
        $hide = round((strlen($dataEmail[0]) / 2));

        return [
            'postal_code' => $data->postal_code,
            'email' => sprintf(
                "%s%s@%s",
                str_repeat("*", $hide),
                substr($dataEmail[0], $hide, $total),
                $dataEmail[1]
            )
        ];
    }

    /**
     * function to get produto to test.
     *
     * @return array
     */
    private function getProductToTest()
    {
        if (empty($_GET['product'])) {
            return false;
        }
        
        return  wc_get_product($_GET['product']);
    }

    /**
     * Get metrics useds in woocommerce.
     *
     * @return array $metrics
     */
    private function getMetrics()
    {
        return [
            'weight_unit' => get_option('woocommerce_weight_unit'),
            'dimension_unit' => get_option('woocommerce_dimension_unit')
        ];
    }

    /**
     * function to return shipping methods selected by user.
     *
     * @return array
     */
    private function getShippingMethods()
    {
        $shippingMethods = (new ShippingMelhorEnvioService())
            ->getMethodsActivedsMelhorEnvio();

        $response = [];

        foreach ($shippingMethods as $item) {
            if ($item->enabled == 'yes') {
                $response[$item->code][] = [
                    'title' => $item->method_title,
                    'custom-title' => $item->title,
                    'additional' => [
                        'additional_tax' => $item->instance_settings['additional_tax'],
                        'percent_tax' => $item->instance_settings['percent_tax'],
                        'additional_time' => $item->instance_settings['additional_time'],
                    ]
                ];
            }
        }

        return $response;
    }
}

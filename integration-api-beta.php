<?php

require __DIR__ . '/vendor/autoload.php';

/*
Plugin Name: SuperFrete
Plugin URI: https://web.superfrete.com
Description: Plugin para cotação e compra de fretes utilizando a API da SuperFrete.
Version: 2.11.29
Author: SuperFrete
Author URI: https://web.superfrete.com
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: baseplugin
Tested up to: 6.0
Requires PHP: 7.2
WC requires at least: 4.0
WC tested up to: 6.2
Domain Path: /languages
*/

/**
 * Copyright (c) YEAR Your Name (email: Email). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__));
}

use IntegrationAPI\Controllers\ShowCalculatorProductPage;
use IntegrationAPI\Models\CalculatorShow;
use IntegrationAPI\Models\Version;
use IntegrationAPI\Services\CheckHealthService;
use IntegrationAPI\Services\ClearDataStored;
use IntegrationAPI\Services\RolesService;
use IntegrationAPI\Services\RouterService;
use IntegrationAPI\Services\ShortCodeService;
use IntegrationAPI\Services\TrackingService;
use IntegrationAPI\Services\ListPluginsIncompatiblesService;
use IntegrationAPI\Services\SessionNoticeService;
use IntegrationAPI\Helpers\SessionHelper;
use IntegrationAPI\Helpers\EscapeAllowedTags;

if (!file_exists(plugin_dir_path(__FILE__) . '/vendor/autoload.php')) {
    $message = 'Erro ao ativar o plugin da SuperFrete, não localizada a vendor do plugin';
    (new SessionNoticeService())->add(
        'Erro ao ativar o plugin da SuperFrete, não localizada a vendor do plugin',
        'notice-error'
    );
    return false;
}

/**
 * Base_Plugin class
 *
 * @class Base_Plugin The class that holds the entire Base_Plugin plugin
 */
final class Base_Plugin
{
    /**
     * Plugin version
     *
     * @var string
     */
    public $version;

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = array();

    /**
     * Constructor for the Base_Plugin class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */
    public function __construct()
    {
        $this->version = Version::VERSION;

        $this->define_constants();

        register_activation_hook(__FILE__, array($this, 'activate'));

        add_action('plugins_loaded', array($this, 'init_plugin'), 9, false);
    }

    /**
     * Initializes the Base_Plugin() class
     *
     * Checks for an existing Base_Plugin() instance
     * and if it doesn't find one, creates it.
     */
    public static function init()
    {

        static $instance = false;

        if (!$instance) {
            $instance = new Base_Plugin();
        }

        return $instance;
    }

    /**
     * Magic getter to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get($prop)
    {
        if (array_key_exists($prop, $this->container)) {
            return $this->container[$prop];
        }

        return $this->{$prop};
    }

    /**
     * Magic isset to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __isset($prop)
    {
        return isset($this->{$prop}) || isset($this->container[$prop]);
    }

    /**
     * Define the constants
     *
     * @return void
     */
    public function define_constants()
    {
        define('BASEPLUGIN_VERSION', $this->version);
        define('BASEPLUGIN_FILE', __FILE__);
        define('BASEPLUGIN_PATH', dirname(BASEPLUGIN_FILE));
        define('BASEPLUGIN_INCLUDES', BASEPLUGIN_PATH . '/includes');
        define('BASEPLUGIN_URL', plugins_url('', BASEPLUGIN_FILE));
        define('BASEPLUGIN_ASSETS', BASEPLUGIN_URL . '/assets');
    }

    /**
     * Load the plugin after all plugis are loaded
     *
     * @return void
     */
    public function init_plugin()
    {
        $this->includes();
        $this->init_hooks();

        $pathPlugins = get_option('integration_api_path_plugins');
        if (!$pathPlugins) {
            $pathPlugins =  WP_PLUGIN_DIR;
        }

        if (is_admin()) {
            (new SessionNoticeService())->showNotices();
            $result = (new CheckHealthService())->checkPathPlugin($pathPlugins);

            if (!empty($result['errors'])) {
                return false;
            }
        }
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate()
    {
        $installed = get_option('baseplugin_installed');

        if (!$installed) {
            update_option('baseplugin_installed', time());
        }

        update_option('baseplugin_version', BASEPLUGIN_VERSION);

        (new ClearDataStored())->clear();
    }

    /**
     * Include the required files
     *
     * @return void
     */
    public function includes()
    {
        try {
            require_once BASEPLUGIN_INCLUDES . '/class-assets-sf.php';

            if ($this->is_request('admin')) {
                require_once BASEPLUGIN_INCLUDES . '/class-admin-sf.php';
            }

            if ($this->is_request('frontend')) {
                require_once BASEPLUGIN_INCLUDES . '/class-frontend-sf.php';
            }

            if ($this->is_request('rest')) {
                require_once BASEPLUGIN_INCLUDES . '/class-rest-api-sf.php';
            }
        } catch (\Exception $e) {
            add_action('admin_sf_notices', function ($e) {
                echo wp_kses(sprintf('<div class="error">
                    <p>%s</p>
                </div>', $e->getMessage()), EscapeAllowedTags::allow_tags(["div", "p"]));
            });
            return false;
        }
    }

    /**
     * Initialize the hooks
     *
     * @return void
     */
    public function init_hooks()
    {
        if (is_admin()) {
            (new CheckHealthService())->init();
            (new RolesService())->init();
        }

        add_action('init', array($this, 'init_classes'));
        add_action('init', array($this, 'localization_setup'));

        (new RouterService())->handler();

        require_once dirname(__FILE__) . '/services_methods/class-wc-integration-api-shipping.php';
        foreach (glob(plugin_dir_path(__FILE__) . 'services_methods/*.php') as $filename) {
            require_once $filename;
        }

        (new TrackingService())->createTrackingColumnOrdersClient();
        $hideCalculator = (new CalculatorShow)->get();
        if ($hideCalculator) {
            (new ShowCalculatorProductPage())->insertCalculator();
        }

        add_filter( 'safe_style_css', function( $styles ) {
            $styles[] = 'display';
            return $styles;
        } );        

        add_filter('woocommerce_shipping_methods', function ($methods) {
            $methods['integrationapi_correios_pac']  = 'WC_Integration_API_Shipping_Correios_Pac';
            $methods['integrationapi_correios_sedex']  = 'WC_Integration_API_Shipping_Correios_Sedex';
            ///$methods['integrationapi_jadlog_package']  = 'WC_Integration_API_Shipping_Jadlog_Package';
            ///$methods['integrationapi_jadlog_com']  = 'WC_Integration_API_Shipping_Jadlog_Com';
            ///$methods['integrationapi_via_brasil_rodoviario']  = 'WC_Integration_API_Shipping_Via_Brasil_Rodoviario';
            ///$methods['integrationapi_latam_juntos']  = 'WC_Integration_API_Shipping_Latam_Juntos';
            ///$methods['integrationapi_azul_amanha']  = 'WC_Integration_API_Shipping_Azul_Amanha';
            ///$methods['integrationapi_azul_ecommerce']  = 'WC_Integration_API_Shipping_Azul_Ecommerce';
            $methods['integrationapi_correios_mini']  = 'WC_Integration_API_Shipping_Correios_Mini';
            ///$methods['integrationapi_buslog_rodoviario']  = 'WC_Integration_API_Shipping_Buslog_Rodoviario';
            return $methods;
        });

        add_filter('woocommerce_package_rates', 'orderingQuotationsByPriceSF', 10, 2);
        function orderingQuotationsByPriceSF($rates, $package)
        {
            uasort($rates, function ($a, $b) {
                if ($a == $b) return 0;
                return ($a->cost < $b->cost) ? -1 : 1;
            });
            return $rates;
        }

        add_action('upgrader_process_complete', function () {
            (new ClearDataStored())->clear();
        });

        if (is_admin()) {
            (new ListPluginsIncompatiblesService())->init();
        }

        function load_var_nonce_sf()
        {
            $wpApiSettings = json_encode( array( 
                'nonce_configs' => wp_create_nonce( 'save_sf_configurations' ),
                'nonce_orders' => wp_create_nonce( 'orders' ),
                'nonce_tokens' => wp_create_nonce( 'tokens' ),
                'nonce_users' => wp_create_nonce( 'users' ),
            ) );
            
            wp_register_script( 'wp-nonce-integration-apii-wp-api', '' );
            wp_enqueue_script( 'wp-nonce-integration-apii-wp-api' );
            wp_add_inline_script( 'wp-nonce-integration-apii-wp-api', "var wpApiSettingsIntegrationAPI = ${wpApiSettings};" );
        }

        add_action( 'admin_enqueue_scripts', 'load_var_nonce_sf');
        add_action( 'wp_enqueue_scripts', 'load_var_nonce_sf');
    }

    /**
     * Instantiate the required classes
     *
     * @return void
     */
    public function init_classes()
    {
        try {
            if ($this->is_request('admin')) {
                $this->container['admin'] = new App\Admin_SF();
            }

            if ($this->is_request('rest')) {
                $this->container['rest'] = new App\REST_API_SF();
            }

            add_shortcode('calculadora_integration_api', function ($attr) {
                if (isset($attr['product_id'])) {
                    $product = wc_get_product($attr['product_id']);
                    if ($product) {
                        (new ShortCodeService($product))->shortcode();
                    }
                }
            });

            $this->container['assets'] = new App\Assets_SF();
        } catch (\Exception $e) {
            add_action('admin_sf_notices', function () use ($e) {
                echo wp_kses(
                    sprintf('<div class="error">
                    <p>%s</p>
                </div>', $e->getMessage()),
                    EscapeAllowedTags::allow_tags(["div", "p"])
                );
            });

            return false;
        }
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup()
    {
        load_plugin_textdomain('baseplugin', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * What type of request is this?
     *
     * @param  string $type admin, ajax, cron or frontend.
     *
     * @return bool
     */
    private function is_request($type)
    {
        switch ($type) {
            case 'admin':
                return is_admin();

            case 'ajax':
                return defined('DOING_AJAX');

            case 'rest':
                return defined('REST_REQUEST');

            case 'cron':
                return defined('DOING_CRON');

            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
        }
    }
} // Base_Plugin

$baseplugin = Base_Plugin::init();

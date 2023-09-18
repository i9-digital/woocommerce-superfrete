<?php

require __DIR__ . '/vendor/autoload.php';

/*
Plugin Name: SuperFrete
Plugin URI: https://superfrete.com
Description: Plugin para cotação e compra de fretes.
Version: 1.0.0
Author: SuperFrete
Author URI: superfrete.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: superfrete
Tested up to: 6.0
Requires PHP: 7.2
WC requires at least: 4.0
WC tested up to: 6.2
Domain Path: /languages
*/

/**
 * Copyright (c) 2023 SuperFrete. All rights reserved.
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

use Superfrete\Controllers\ShowCalculatorProductPage;
use Superfrete\Models\CalculatorShow;
use Superfrete\Models\Version;
use Superfrete\Services\CheckHealthService;
use Superfrete\Services\ClearDataStored;
use Superfrete\Services\RolesService;
use Superfrete\Services\RouterService;
use Superfrete\Services\ShortCodeService;
use Superfrete\Services\TrackingService;
use Superfrete\Services\ListPluginsIncompatiblesService;
use Superfrete\Services\SessionNoticeService;
use Superfrete\Helpers\SessionHelper;
use Superfrete\Helpers\EscapeAllowedTags;

if (!file_exists(plugin_dir_path(__FILE__) . '/vendor/autoload.php')) {
    $message = 'Erro ao ativar o plugin da SuperFrete, não localizada a vendor do plugin';
    (new SessionNoticeService())->add(
        'Erro ao ativar o plugin da SuperFrete, não localizada a vendor do plugin',
        'notice-error'
    );
    return false;
}

/**
 * Superfrete_Plugin class
 *
 * @class Superfrete_Plugin The class that holds the entire Superfrete_Plugin plugin
 */
final class Superfrete_Plugin
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
     * Constructor for the Superfrete_Plugin class
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
     * Initializes the Superfrete_Plugin() class
     *
     * Checks for an existing Superfrete_Plugin() instance
     * and if it doesn't find one, creates it.
     */
    public static function init()
    {

        static $instance = false;

        if (!$instance) {
            $instance = new Superfrete_Plugin();
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
        define('SUPERFRETE_VERSION', $this->version);
        define('SUPERFRETE_FILE', __FILE__);
        define('SUPERFRETE_PATH', dirname(SUPERFRETE_FILE));
        define('SUPERFRETE_INCLUDES', SUPERFRETE_PATH . '/includes');
        define('SUPERFRETE_URL', plugins_url('', SUPERFRETE_FILE));
        define('SUPERFRETE_ASSETS', SUPERFRETE_URL . '/assets');
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

        $pathPlugins = get_option('superfrete_path_plugins');
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
        $installed = get_option('superfrete_installed');

        if (!$installed) {
            update_option('superfrete_installed', time());
        }

        update_option('superfrete_version', SUPERFRETE_VERSION);

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
            require_once SUPERFRETE_INCLUDES . '/class-assets-superfrete.php';

            if ($this->is_request('admin')) {
                require_once SUPERFRETE_INCLUDES . '/class-admin-superfrete.php';
            }

            if ($this->is_request('frontend')) {
                require_once SUPERFRETE_INCLUDES . '/class-frontend-superfrete.php';
            }

            if ($this->is_request('rest')) {
                require_once SUPERFRETE_INCLUDES . '/class-rest-api-superfrete.php';
            }
        } catch (\Exception $e) {
            add_action('admin_superfrete_notices', function ($e) {
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

        require_once dirname(__FILE__) . '/services_methods/class-wc-superfrete-shipping.php';
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
            $methods['superfrete_correios_pac']  = 'WC_Superfrete_Shipping_Correios_Pac';
            $methods['superfrete_correios_sedex']  = 'WC_Superfrete_Shipping_Correios_Sedex';
            $methods['superfrete_correios_mini']  = 'WC_Superfrete_Shipping_Correios_Mini';
            return $methods;
        });

        add_filter('woocommerce_package_rates', 'orderingQuotationsByPriceSuperfrete', 10, 2);
        function orderingQuotationsByPriceSuperfrete($rates, $package)
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

        function load_var_nonce_superfrete()
        {
            $wpApiSettings = json_encode( array( 
                'nonce_configs' => wp_create_nonce( 'save_superfrete_configurations' ),
                'nonce_orders' => wp_create_nonce( 'orders' ),
                'nonce_tokens' => wp_create_nonce( 'tokens' ),
                'nonce_users' => wp_create_nonce( 'users' ),
            ) );
            
            wp_register_script( 'wp-nonce-superfretei-wp-api', '' );
            wp_enqueue_script( 'wp-nonce-superfretei-wp-api' );
            wp_add_inline_script( 'wp-nonce-superfretei-wp-api', "var wpApiSettingsSuperfrete = ${wpApiSettings};" );
        }

        add_action( 'admin_enqueue_scripts', 'load_var_nonce_superfrete');
        add_action( 'wp_enqueue_scripts', 'load_var_nonce_superfrete');
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
                $this->container['admin'] = new App\Admin_SUPERFRETE();
            }

            if ($this->is_request('rest')) {
                $this->container['rest'] = new App\REST_API_SUPERFRETE();
            }

            add_shortcode('calculadora_superfrete', function ($attr) {
                if (isset($attr['product_id'])) {
                    $product = wc_get_product($attr['product_id']);
                    if ($product) {
                        (new ShortCodeService($product))->shortcode();
                    }
                }
            });

            $this->container['assets'] = new App\Assets_SUPERFRETE();
        } catch (\Exception $e) {
            add_action('admin_superfrete_notices', function () use ($e) {
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
        load_plugin_textdomain('superfrete', false, dirname(plugin_basename(__FILE__)) . '/languages/');
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
} // Superfrete_Plugin

$superfrete = Superfrete_Plugin::init();

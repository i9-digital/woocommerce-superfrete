<?php
$envConfig = parse_ini_file('.env');

define('SUPERFRETE_CONFIG_PLATFORM', 'WooCommerce V2');

define('SUPERFRETE_CONFIG_URL', 'https://api.superfrete.com');
define('SUPERFRETE_CONFIG_SANDBOX_URL', (!empty($envConfig['SUPERFRETE_SANDBOX_URL'])) ? $envConfig['SUPERFRETE_SANDBOX_URL'] : 'https://sandbox.superfrete.com');

define('SUPERFRETE_CONFIG_TIMEOUT', 60);
define('SUPERFRETE_CONFIG_DEFAULT_METHOD_ID', 'superfrete_correios_sedex');
define('SUPERFRETE_CONFIG_REASON_CANCELED_USER', 2);
define('SUPERFRETE_CONFIG_TIME_DURATION_SESSION_QUOTATION_IN_SECONDS', 900);

if($envConfig['SUPERFRETE_ENDPOINT_PATH'] == 'FUNCTIONS') {
  define('SUPERFRETE_CONFIG_ROUTE_USER_BALANCE', '/apiIntegrationV1UserGetBalance');
  define('SUPERFRETE_CONFIG_ROUTE_USER_INFO', '/apiIntegrationV1UserGetInfo');
  define('SUPERFRETE_CONFIG_ROUTE_CANCEL', '/apiIntegrationV1OrderCancel');
  define('SUPERFRETE_CONFIG_ROUTE_CANCELLABLE', '/apiIntegrationV1OrderCancellable');
  define('SUPERFRETE_CONFIG_ROUTE_TRACKING', '/apiIntegrationV1Tracking');
  define('SUPERFRETE_CONFIG_ROUTE_CART', '/apiIntegrationV1Cart');
  define('SUPERFRETE_CONFIG_ROUTE_CHECKOUT', '/apiIntegrationV1Checkout');
  define('SUPERFRETE_CONFIG_ROUTE_CREATE_LABEL', '/apiIntegrationV1GenerateTag');
  define('SUPERFRETE_CONFIG_ROUTE_PRINT_LABEL', '/apiIntegrationV1GenerateTagLink');
  define('SUPERFRETE_CONFIG_ROUTE_SEARCH', '/apiIntegrationV1OrderGetInfo/');
  define('SUPERFRETE_CONFIG_ROUTE_CALCULATE', '/apiIntegrationV1Calculator');
  define('SUPERFRETE_CONFIG_ROUTE_COMPANIES', '/apiIntegrationV1UserGetStores');
  define('SUPERFRETE_CONFIG_ROUTE_ADDRESS', '/apiIntegrationV1UserGetAddresses');
  define('SUPERFRETE_CONFIG_ROUTE_GET_AGENCIES', '/shipment/agencies');
}

if($envConfig['SUPERFRETE_ENDPOINT_PATH'] == 'FUNCTIONS_AND_API_URL') {
  define('SUPERFRETE_CONFIG_ROUTE_USER_BALANCE', '/apiIntegrationV1UserGetBalance/api/v0/user/balance');
  define('SUPERFRETE_CONFIG_ROUTE_USER_INFO', '/apiIntegrationV1UserGetInfo/api/v0/user');
  define('SUPERFRETE_CONFIG_ROUTE_CANCEL', '/apiIntegrationV1OrderCancel/api/v0/order/cancel');
  define('SUPERFRETE_CONFIG_ROUTE_CANCELLABLE', '/apiIntegrationV1OrderCancellable/api/v0/order/cancellable');
  define('SUPERFRETE_CONFIG_ROUTE_TRACKING', '/apiIntegrationV1Tracking/api/v0/tag/tracking');
  define('SUPERFRETE_CONFIG_ROUTE_CART', '/apiIntegrationV1Cart/api/v0/cart');
  define('SUPERFRETE_CONFIG_ROUTE_CHECKOUT', '/apiIntegrationV1Checkout/api/v0/checkout');
  define('SUPERFRETE_CONFIG_ROUTE_CREATE_LABEL', '/apiIntegrationV1GenerateTag/api/v0/tag/generate');
  define('SUPERFRETE_CONFIG_ROUTE_PRINT_LABEL', '/apiIntegrationV1GenerateTagLink/api/v0/tag/print');
  define('SUPERFRETE_CONFIG_ROUTE_SEARCH', '/apiIntegrationV1OrderGetInfo/api/v0/order/info/');
  define('SUPERFRETE_CONFIG_ROUTE_CALCULATE', '/apiIntegrationV1Calculator/api/v0/calculator');
  define('SUPERFRETE_CONFIG_ROUTE_COMPANIES', '/apiIntegrationV1UserGetStores/api/v0/user/stores');
  define('SUPERFRETE_CONFIG_ROUTE_ADDRESS', '/apiIntegrationV1UserGetAddresses/api/v0/user/addresses');
  define('SUPERFRETE_CONFIG_ROUTE_GET_AGENCIES', '/shipment/agencies');
}

if($envConfig['SUPERFRETE_ENDPOINT_PATH'] == 'API_URL' || empty($envConfig['ENDPOINT_PATH'])) {
  define('SUPERFRETE_CONFIG_ROUTE_USER_BALANCE', '/api/v0/user/balance');
  define('SUPERFRETE_CONFIG_ROUTE_USER_INFO', '/api/v0/user');
  define('SUPERFRETE_CONFIG_ROUTE_CANCEL', '/api/v0/order/cancel');
  define('SUPERFRETE_CONFIG_ROUTE_CANCELLABLE', '/api/v0/order/cancellable');
  define('SUPERFRETE_CONFIG_ROUTE_TRACKING', '/api/v0/tag/tracking');
  define('SUPERFRETE_CONFIG_ROUTE_CART', '/api/v0/cart');
  define('SUPERFRETE_CONFIG_ROUTE_CHECKOUT', '/api/v0/checkout');
  define('SUPERFRETE_CONFIG_ROUTE_CREATE_LABEL', '/api/v0/tag/generate');
  define('SUPERFRETE_CONFIG_ROUTE_PRINT_LABEL', '/api/v0/tag/print');
  define('SUPERFRETE_CONFIG_ROUTE_SEARCH', '/api/v0/order/info/');
  define('SUPERFRETE_CONFIG_ROUTE_CALCULATE', '/api/v0/calculator');
  define('SUPERFRETE_CONFIG_ROUTE_COMPANIES', '/api/v0/user/stores');
  define('SUPERFRETE_CONFIG_ROUTE_ADDRESS', '/api/v0/user/addresses');
  define('SUPERFRETE_CONFIG_ROUTE_GET_AGENCIES', '/shipment/agencies');
}

?>
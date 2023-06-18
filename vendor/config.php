<?php
$envConfig = parse_ini_file('.env');

define('CONFIG_PLATFORM', 'WooCommerce V2');

define('CONFIG_URL', 'https://api.superfrete.com');
define('CONFIG_SANDBOX_URL', (!empty($envConfig['SANDBOX_URL'])) ? $envConfig['SANDBOX_URL'] : 'https://sandbox.superfrete.com');

define('CONFIG_TIMEOUT', 60);
define('CONFIG_DEFAULT_METHOD_ID', 'integrationapi_correios_sedex');
define('CONFIG_REASON_CANCELED_USER', 2);
define('CONFIG_TIME_DURATION_SESSION_QUOTATION_IN_SECONDS', 900);

if($envConfig['ENDPOINT_PATH'] == 'FUNCTIONS') {
  define('CONFIG_ROUTE_INTEGRATION_API_USER_BALANCE', '/apiIntegrationV1UserGetBalance');
  define('CONFIG_ROUTE_INTEGRATION_API_USER_INFO', '/apiIntegrationV1UserGetInfo');
  define('CONFIG_ROUTE_INTEGRATION_API_CANCEL', '/apiIntegrationV1OrderCancel');
  define('CONFIG_ROUTE_INTEGRATION_API_CANCELLABLE', '/apiIntegrationV1OrderCancellable');
  define('CONFIG_ROUTE_INTEGRATION_API_TRACKING', '/apiIntegrationV1Tracking');
  define('CONFIG_ROUTE_INTEGRATION_API_CART', '/apiIntegrationV1Cart');
  define('CONFIG_ROUTE_INTEGRATION_API_CHECKOUT', '/apiIntegrationV1Checkout');
  define('CONFIG_ROUTE_INTEGRATION_API_CREATE_LABEL', '/apiIntegrationV1GenerateTag');
  define('CONFIG_ROUTE_INTEGRATION_API_PRINT_LABEL', '/apiIntegrationV1GenerateTagLink');
  define('CONFIG_ROUTE_INTEGRATION_API_SEARCH', '/apiIntegrationV1OrderGetInfo/');
  define('CONFIG_ROUTE_INTEGRATION_API_CALCULATE', '/apiIntegrationV1Calculator');
  define('CONFIG_ROUTE_INTEGRATION_API_COMPANIES', '/apiIntegrationV1UserGetStores');
  define('CONFIG_ROUTE_INTEGRATION_API_ADDRESS', '/apiIntegrationV1UserGetAddresses');
  define('CONFIG_ROUTE_GET_AGENCIES', '/shipment/agencies');
}

if($envConfig['ENDPOINT_PATH'] == 'FUNCTIONS_AND_API_URL') {
  define('CONFIG_ROUTE_INTEGRATION_API_USER_BALANCE', '/apiIntegrationV1UserGetBalance/api/v0/user/balance');
  define('CONFIG_ROUTE_INTEGRATION_API_USER_INFO', '/apiIntegrationV1UserGetInfo/api/v0/user');
  define('CONFIG_ROUTE_INTEGRATION_API_CANCEL', '/apiIntegrationV1OrderCancel/api/v0/order/cancel');
  define('CONFIG_ROUTE_INTEGRATION_API_CANCELLABLE', '/apiIntegrationV1OrderCancellable/api/v0/order/cancellable');
  define('CONFIG_ROUTE_INTEGRATION_API_TRACKING', '/apiIntegrationV1Tracking/api/v0/tag/tracking');
  define('CONFIG_ROUTE_INTEGRATION_API_CART', '/apiIntegrationV1Cart/api/v0/cart');
  define('CONFIG_ROUTE_INTEGRATION_API_CHECKOUT', '/apiIntegrationV1Checkout/api/v0/checkout');
  define('CONFIG_ROUTE_INTEGRATION_API_CREATE_LABEL', '/apiIntegrationV1GenerateTag/api/v0/tag/generate');
  define('CONFIG_ROUTE_INTEGRATION_API_PRINT_LABEL', '/apiIntegrationV1GenerateTagLink/api/v0/tag/print');
  define('CONFIG_ROUTE_INTEGRATION_API_SEARCH', '/apiIntegrationV1OrderGetInfo/api/v0/order/info/');
  define('CONFIG_ROUTE_INTEGRATION_API_CALCULATE', '/apiIntegrationV1Calculator/api/v0/calculator');
  define('CONFIG_ROUTE_INTEGRATION_API_COMPANIES', '/apiIntegrationV1UserGetStores/api/v0/user/stores');
  define('CONFIG_ROUTE_INTEGRATION_API_ADDRESS', '/apiIntegrationV1UserGetAddresses/api/v0/user/addresses');
  define('CONFIG_ROUTE_GET_AGENCIES', '/shipment/agencies');
}

if($envConfig['ENDPOINT_PATH'] == 'API_URL' || empty($envConfig['ENDPOINT_PATH'])) {
  define('CONFIG_ROUTE_INTEGRATION_API_USER_BALANCE', '/api/v0/user/balance');
  define('CONFIG_ROUTE_INTEGRATION_API_USER_INFO', '/api/v0/user');
  define('CONFIG_ROUTE_INTEGRATION_API_CANCEL', '/api/v0/order/cancel');
  define('CONFIG_ROUTE_INTEGRATION_API_CANCELLABLE', '/api/v0/order/cancellable');
  define('CONFIG_ROUTE_INTEGRATION_API_TRACKING', '/api/v0/tag/tracking');
  define('CONFIG_ROUTE_INTEGRATION_API_CART', '/api/v0/cart');
  define('CONFIG_ROUTE_INTEGRATION_API_CHECKOUT', '/api/v0/checkout');
  define('CONFIG_ROUTE_INTEGRATION_API_CREATE_LABEL', '/api/v0/tag/generate');
  define('CONFIG_ROUTE_INTEGRATION_API_PRINT_LABEL', '/api/v0/tag/print');
  define('CONFIG_ROUTE_INTEGRATION_API_SEARCH', '/api/v0/order/info/');
  define('CONFIG_ROUTE_INTEGRATION_API_CALCULATE', '/api/v0/calculator');
  define('CONFIG_ROUTE_INTEGRATION_API_COMPANIES', '/api/v0/user/stores');
  define('CONFIG_ROUTE_INTEGRATION_API_ADDRESS', '/api/v0/user/addresses');
  define('CONFIG_ROUTE_GET_AGENCIES', '/shipment/agencies');
}

?>
<?php

namespace Services;

use Models\Option;

/**
 * Quotation service class
 */
class QuotationService
{
    /**
     * Melhor Envio api route to make the quote
     */
    const ROUTE_API_MELHOR_CALCULATE = '/shipment/calculate';

    /**
     * Function to calculate a quotation by order_id.
     *
     * @param int $order_id id of post wordpress
     * @return object $quotation
     */
    public function calculateQuotationByOrderId($order_id)
    {
        $products = (new OrdersProductsService())->getProductsOrder($order_id);

        $buyer = (new BuyerService())->getDataBuyerByOrderId($order_id);

        $quotation = $this->calculateQuotationByProducts($products, $buyer->postal_code, null);

        return (new OrderQuotationService())->saveQuotation($order_id, $quotation);
    }

    /**
     * Function to calculate a quotation by products.
     *
     * @param array $products  
     * @param  string $postal_code
     * @param int $service
     * @return  object $quotation
     */
    public function calculateQuotationByProducts(
        $products,
        $postal_code,
        $service = null
    ) {
        $seller = (new SellerService())->getData();

        $body = [
            'from' => [
                'postal_code' => $seller->postal_code,
            ],
            'to' => [
                'postal_code' => $postal_code
            ],
            'options'  => (new Option())->getOptions(),
            'products' => $products
        ];

        $quotation = $this->_getSessionCachedQuotation($body, $service);

        if (!$quotation) {

            $quotation = (new RequestService())->request(
                self::ROUTE_API_MELHOR_CALCULATE,
                'POST',
                $body,
                true
            );

            $this->storeQuotationSession($body, $quotation);
        }

        return $quotation;
    }

    /**
     * Function to calculate a quotation by packages.
     *
     * @param array $packages
     * @param string $postal_code
     * @return object $quotation
     */
    public function calculateQuotationByPackages(
        $packages,
        $postal_code,
        $service = null
    ) {
        $seller = (new SellerService())->getData();

        $body = [
            'from' => [
                'postal_code' => $seller->postal_code,
            ],
            'to' => [
                'postal_code' => $postal_code
            ],
            'options'  => (new Option())->getOptions(),
            'packages' => $packages
        ];

        $quotation = $this->_getSessionCachedQuotation($body, $service);

        if (!$quotation) {

            $quotation = (new RequestService())->request(
                self::ROUTE_API_MELHOR_CALCULATE,
                'POST',
                $body,
                true
            );

            $this->storeQuotationSession($body, $quotation);
        }

        return $quotation;
    }

    /**
     * Function to save response quotation on session.
     *
     * @param array $bodyQuotation
     * @param array $quotation
     * @return void
     */
    private function storeQuotationSession($bodyQuotation, $quotation)
    {
        session_start();
        $hash = md5(json_encode($bodyQuotation));
        $_SESSION['quotation'][$hash] = $quotation;
        $_SESSION['quotation'][$hash]['created'] = date('Y-m-d h:i:s');
    }

    /**
     * Function to search for the quotation of a shipping service in the session, 
     * if it does not find false returns
     *
     * @param array $bodyQuotation
     * @param int $service
     * @return bool|array
     */
    private function _getSessionCachedQuotation($bodyQuotation, $service)
    {
        session_start();

        $hash = md5(json_encode($bodyQuotation));

        if (!isset($_SESSION['quotation'][$hash][$service])) {
            unset($_SSESION['quotation'][$hash]);
            return false;
        }

        if ($this->_isSessionCachedQuotationExpired($bodyQuotation)) {
            return false;
        }

        return end(array_filter(
            $_SESSION['quotation'][$hash],
            function ($item) use ($service) {
                if ($item->id == $service) {
                    return $item;
                }
            }
        ));
    }

    /**
     * Function to see if the session quote should expire due to the time
     *
     * @param array $bodyQuotation payload to make quotation on Melhor Envio api
     * @return boolean
     */
    private function _isSessionCachedQuotationExpired($bodyQuotation)
    {
        $hash = md5(json_encode($bodyQuotation));

        if (!isset($_SESSION['quotation'][$hash]['created'])) {
            return true;
        }

        $created = $_SESSION['quotation'][$hash]['created'];

        $dateLimit = date('Y-m-d H:i:s', strtotime('-15 minutes'));

        if ($dateLimit > $created) {
            unset($_SESSION['quotation'][$hash]);

            return true;
        }

        return false;
    }
}

<?php

namespace Controllers;

class PackageController 
{
    /**
     * @param [type] $package
     * @return void
     */
    public function getPackage($package) 
    {
        $weight = 0;
        $width = 0;
        $height = 0;
        $length = 0;

        foreach ($package['contents'] as $item_id => $values) {
            
            $_product = $values['data'];
            $weight = $weight + $_product->get_weight() * $values['quantity'];

            $width  += $_product->width;
            $height += $_product->height;
            $length += $_product->length;
        }
        
        return $this->converterIfNecessary([
            "weight" => $weight,
            "width" => $width,
            "height" => $height,
            "length" => $length
        ]);
    }

    /**
     * @param [type] $order_id
     * @return void
     */
    public function getPackageOrderAfterCotation($order_id) 
    {
        $data = get_post_meta($order_id, 'melhorenvio_cotation_v2', true);

        $packages = [];
        if (is_array($data)) {
            foreach ($data as $item) {
                if(!isset($item->packages)) {
                    continue;
                }
                if(!empty($item->packages)) {

                    $total = $this->countTotalvolumes($item->packages);
                    $volumes = count($item->packages);
                    $v = 1;
                    foreach ($item->packages as $package) {
                        $quantity = (isset($package->products[0]->quantity)) ? $package->products[0]->quantity : 1;
                        $weight = (isset($package->weight)) ? $package->weight : null;

                        $packages[$item->id][] = [
                            'volume' => $v,
                            'width'  => (isset($package->dimensions->width)) ? $package->dimensions->width : null,
                            'height' => (isset($package->dimensions->height)) ? $package->dimensions->height : null,
                            'length' => (isset($package->dimensions->length)) ? $package->dimensions->length : null,
                            'weight' => $this->getWeighteBox($total, $quantity, $weight),
                            'quantity' => $quantity,
                            'insurnace_value' => $this->getInsuranceBox($total, $quantity, $item->custom_price)
                        ];

                        $v++;
                    }
                }
            }
        }

        return $packages;
    }

    private function countTotalvolumes($data)
    {
        $total = 0;
        foreach ($data as $item) {
            foreach($item->products as $prod) {
                $total = $total + $prod->quantity;
            }
        }
        return $total;
    }

    private function getInsuranceBox($total, $quantity, $value)
    {
        $unitValue = $value / $total;
        return $unitValue * $quantity;
    }

    private function getWeighteBox($total, $quantity, $value)
    {
        $unit = $value / $total;
        return $unit * $quantity;
    }

    /**
     * @param [type] $order_id
     * @return void
     */
    public function getPackageOrder($order_id) 
    {
        $weight = 0;
        $width  = 0;
        $height = 0;
        $length = 0;
        $order  = wc_get_order( $order_id );

        foreach( $order->get_items() as $item_id => $item_product ){

            $product_id = $item_product->get_product_id();
            $_product = $item_product->get_product();

            $weight = $weight + $_product->weight * $item_product->get_quantity();
            $width  += $_product->width;
            $height += $_product->height;
            $length += $_product->length;
        }

        return $this->converterIfNecessary([
            "weight" => $weight,
            "width"  => $width,
            "height" => $height,
            "length" => $length
        ]);
    }

    /**
     * @param [type] $package
     * @return void
     */
    private function converterIfNecessary($package) 
    {
        $weight_unit = get_option('woocommerce_weight_unit');
        if ($weight_unit == 'g') {
            $package['weight'] = $package['weight'] / 1000;
        }
        return $package;
    }

}

<?php

namespace GingerPlugins\Components\Classes;

use GingerPlugin\Components\Classes\Redefiner;
use GingerPlugins\Components\Configurators\BankConfig;
use GingerPlugins\Components\Traits\WebRequestTrait;
use GingerPlugins\Exceptions\InvalidApiResponse;

class OrderBuilder extends Helper
{
    use WebRequestTrait;

    const PHYSICAL = 'physical';

    const SHIPPING_FEE = 'shipping_fee';

    /**
     * @param object $cart
     * @return array|null
     * @throws \GingerPlugins\Exceptions\InvalidApiResponse
     */
    public function getOrderLines(object $cart): ?array
    {
        $products = json_decode($this->makeWebRequest('orders/' . $cart->order_id . '/orderrows', 'GET'));

        $orderLines = [];
        foreach ($products->items as $product) {
            $productPhotos = json_decode(
                $this->makeWebRequest('products/' . $product->product_id . '/productphotos/', 'GET')
            );

            $product_info = [
                'url' => $product->product_href,
                'name' => $product->product_name,
                'type' => Redefiner::PHYSICAL,
                'amount' => $this->getAmountInCents(round($product->price, 2) + round($product->price * $product->tax / 100, 2)),
                'currency' => $cart->currency,
                'quantity' => (int)$product->count,
                'vat_percentage' => ((int)$product->tax * 100),
                'merchant_order_line_id' => (string)$product->product_id
            ];

            if (isset($productPhotos->items[0]->deeplink)) {
                $product_info['image_url'] = $productPhotos->items[0]->deeplink;
            }

            $orderLines[] = array_filter(
                $product_info,
                function ($var) {
                    return !is_null($var);
                }
            );
        }

        $order_info = json_decode($this->makeWebRequest('orders/' . $cart->order_id, 'GET'));
        $shippingFee = $order_info->total_shipping;

        if ($shippingFee > 0) {
            $orderLines[] = $this->getShippingOrderLine(
                count($products->items),
                $shippingFee,
                $order_info->shipping_tax_percentage,
                $cart->currency
            );
        }

        return count($orderLines) > 0 ? $orderLines : null;
    }

    /**
     * @param int $productsAmount
     * @param int $shippingFee
     * @param int $shippingTax
     * @param string $currency
     * @return array
     */
    public function getShippingOrderLine(int $productsAmount, int $shippingFee, int $shippingTax, string $currency): array
    {
        return [
            'name' => "Shipping Fee",
            'type' => Redefiner::SHIPPING_FEE,
            'amount' => $this->getAmountInCents($shippingFee),
            'currency' => $currency,
            'vat_percentage' => $this->getAmountInCents($shippingTax),
            'quantity' => 1,
            'merchant_order_line_id' => $productsAmount + 1
        ];
    }

    /**
     * Method formats the floating point amount to amount in cents
     *
     * @param numeric $total
     * @return int
     */
    public function getAmountInCents($total): int
    {
        return (int)round($total * 100);
    }

    /**
     * Generate order description
     *
     * @param string $orderNumber
     * @return string
     */
    public function getOrderDescription(string $orderNumber): string
    {
        return sprintf('Your order %s at WebShop', $orderNumber);
    }

    /**
     * Preparing a transaction array for order creation
     *
     * @param $payment_method
     * @param $issuer_id
     * @return array[]
     */
    public function getTransactionsArray($payment_method, $issuer_id): array
    {
        return array_filter([
            array_filter([
                'payment_method' => BankConfig::CCVSHOP_TO_BANK_PAYMENTS[$payment_method],
                'payment_method_details' => array_filter([
                    'issuer_id' => (string)$issuer_id
                ])
            ])
        ]);

    }

    /**
     * Prepare a webhook url based on storeId and Webhook.php location
     *
     * @param string $order_number
     * @return string
     * @throws InvalidApiResponse
     */
    public function getWebHookUrl(string $order_number): string
    {
        return BankConfig::AppUri . "/webhook" . "?storeId=" . $this->getStoreId() . "&order_number=" . $order_number;
    }

    /**
     * Method returns customer information from the order
     *
     * @param object $cart
     * @return array
     */
    public function getCustomerInfo(object $cart): array
    {
        $shipping_address = $cart->shipping_address;
        $billing_address = $cart->billing_address;

        if (!empty($shipping_address->street)) {
            $address = trim($shipping_address->street)
                . ' ' . trim($shipping_address->house_number)
                . ' ' . trim($shipping_address->house_extension)
                . ' ' . trim($shipping_address->postal_code)
                . ' ' . trim($shipping_address->city);
        } else {
            $address = trim($billing_address->street)
                . ' ' . trim($billing_address->house_number)
                . ' ' . trim($billing_address->house_extension)
                . ' ' . trim($billing_address->postal_code)
                . ' ' . trim($billing_address->city);
        }

        return array_filter(
            [
                'address_type' => 'customer',
                'email_address' => $billing_address->email,
                'first_name' => $billing_address->first_name,
                'last_name' => $billing_address->last_name,
                'address' => $address,
                'postal_code' => $shipping_address->postal_code ? $shipping_address->postal_code : $billing_address->postal_code,
                'country' => $shipping_address->country ? $shipping_address->country : $billing_address->country,
                'phone_numbers' => array_filter([$billing_address->phone_number]),
                'ip_address' => $cart->ipv4_address,
                'locale' => $cart->language,
                'gender' => $billing_address->gender == 'M' ? 'male' : 'female',
                'birthdate' => $cart->date_of_birth,
                'additional_addresses' => [
                    [
                        'address_type' => 'billing',
                        'address' => trim($billing_address->street)
                            . ' ' . trim($billing_address->house_number)
                            . ' ' . trim($billing_address->house_extension)
                            . ' ' . trim($billing_address->postal_code)
                            . ' ' . trim($billing_address->city),
                        'country' => $billing_address->country,
                    ]
                ]
            ]
        );
    }
}
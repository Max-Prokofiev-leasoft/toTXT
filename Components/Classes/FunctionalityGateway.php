<?php

namespace GingerPlugins\Components\Classes;

use GingerPlugin\Components\Classes\PluginGateway;
use GingerPlugin\Components\Classes\Redefiner;
use GingerPlugins\Classes\CodeBlockTrait;
use GingerPlugins\Components\Configurators\BankConfig;
use GingerPlugins\Components\Traits\WebRequestTrait;
use GingerPlugins\Data\Data_Credential;
use GingerPlugins\Exceptions\InvalidGingerOrderId;
use GingerPlugins\Exceptions\InvalidGingerOrderStatus;
use GingerPlugins\Exceptions\InvalidGingerOrderPaymentUrl;
use GingerPlugins\Exceptions\InvalidCredentialException;
use GingerPlugins\Exceptions\InvalidApiResponse;
use GingerPlugins\Data\Translations;

class FunctionalityGateway extends PluginGateway
{
    use CodeBlockTrait;
    use WebRequestTrait;

    protected mixed $payment_method;

    /**
     * @throws InvalidCredentialException
     * @throws InvalidApiResponse
     */
    public function __construct($method = '', $credential = null)
    {
        $this->payment_method = $method;

        $this->credentials = $credential ?? Data_Credential::GetOneByStoreId($this->getStoreId());

        parent::__construct($this->payment_method, $this->credentials);
    }

    /**
     * @param object $cart
     * @return array
     * @throws \GingerPlugins\Exceptions\InvalidApiResponse
     * @throws \GingerPlugins\Exceptions\InvalidGingerOrderId
     * @throws \GingerPlugins\Exceptions\InvalidGingerOrderPaymentUrl
     * @throws \GingerPlugins\Exceptions\InvalidGingerOrderStatus
     */
    public function createOrder(object $cart): array
    {
        $webhook_url = $this->getWebHookUrl($cart->order_number);
        $issuerId = $cart->issuer ?? '';
        try {
            $order_details = array_filter(
                [
                    'amount' => $this->getAmountInCents($cart->amount),
                    'currency' => $cart->currency,
                    'transactions' => $this->getTransactionsArray($this->payment_method, $issuerId),
                    'merchant_order_id' => (string)$cart->order_number,
                    'description' => $this->getOrderDescription($cart->order_number),
                    'return_url' => $cart->return_url,
                    'customer' => $this->getCustomerInfo($cart),
                    'extra' => [
                        'plugin' => BankConfig::PLUGIN_VERSION,
                        'ipv4_address' => $cart->ipv4_address,
                        'webhook_url' => $cart->webhook_url,
                    ],
                    'webhook_url' => $webhook_url
                ]
            );
            $order_details['order_lines'] = $this->getOrderLines($cart);

            $gingerOrder = $this->client->createOrder($order_details);
        } catch (\Exception $oEx) {
            Helper::logGingerError(__FILE__, 'Functionality Gateway', $oEx->getMessage());
            $this->addCodeblocks($oEx->getMessage());

            header('HTTP/1.1 500 Internal Server Error', true, 500);
            echo $oEx->getMessage();
            die();
        }

        $this->validateGingerOrder($gingerOrder, $cart->language);

        if ($this->payment_method == 'banktransfer') {
            $this->addPaymentInfoToEmail($cart->order_id, $cart->order_number);
        }

        if (in_array($this->payment_method, BankConfig::GINGER_CAPTURE_PAYMENTS)) {
            $this->addInfoToCurrentOrder((string)$cart->order_id, $gingerOrder['id']);
        }
        return $gingerOrder;
    }

    /**
     * Validation Ginger order
     * @param array $gingerOrder
     * @param string $language
     * @throws \GingerPlugins\Exceptions\InvalidApiResponse
     * @throws \GingerPlugins\Exceptions\InvalidGingerOrderId
     * @throws \GingerPlugins\Exceptions\InvalidGingerOrderPaymentUrl
     * @throws \GingerPlugins\Exceptions\InvalidGingerOrderStatus
     */
    public function validateGingerOrder(array $gingerOrder, string $language)
    {
        $translations = Translations::getTranslations($language);
        $gingerCurrentTransaction = current($gingerOrder['transactions']);

        if ($gingerOrder['status'] == 'error') {
            Helper::logGingerError(
                __FILE__,
                'Functionality Gateway',
                'Ginger Order status: ' . $gingerOrder['status'] . ' Reason: ' . $gingerCurrentTransaction['customer_message']
            );
            $this->addCodeblocks(
                $translations['GINGER_ORDERSTATUS'] . $gingerOrder['status'] . $translations['GINGER_REASON'] . $gingerCurrentTransaction['customer_message']
            );

            header('HTTP/1.1 500 Internal Server Error', true, 500);
            throw new InvalidGingerOrderStatus(
                $translations['GINGER_ORDERSTATUS'] . $gingerOrder['status'] . $translations['GINGER_REASON'] . $gingerCurrentTransaction['customer_message']
            );
        }
        if (!array_key_exists('id', $gingerOrder)) {
            Helper::logGingerError(__FILE__, 'Functionality Gateway', 'Ginger Order ID not found');
            $this->addCodeblocks($translations['GINGER_NOID']);

            header('HTTP/1.1 500 Internal Server Error', true, 500);
            throw new InvalidGingerOrderId();
        }

        if ($this->payment_method == 'banktransfer') {
            $pay_url = $gingerOrder['return_url'];
        } else {
            $pay_url = $gingerCurrentTransaction['payment_url'] ?? ($gingerOrder['order_url'] ?? null);
        }

        if (!$pay_url) {
            Helper::logGingerError(__FILE__, 'Functionality Gateway', 'Payment url not found after Ginger Order create attempt');
            $this->addCodeblocks($translations['GINGER_NOPAYURL']);

            header('HTTP/1.1 500 Internal Server Error', true, 500);
            throw new InvalidGingerOrderPaymentUrl();
        }
    }

    /**
     * Save CCV Shop and Ginger ids of existing order to DB
     * @param string $ccvOrderId
     * @param string $gingerOrderId
     * @throws \GingerPlugins\Exceptions\InvalidApiResponse
     */
    public function addInfoToCurrentOrder(string $ccvOrderId, string $gingerOrderId)
    {
        $orderNote = new \stdClass();
        $orderNote->note = $gingerOrderId;

        $this->makeWebRequest('orders/' . $ccvOrderId . '/ordernotes/', 'POST', $orderNote);
    }

    /**
     * Add Payment info to the customer's email
     * @param string $orderId
     * @param $reference
     * @throws \GingerPlugins\Exceptions\InvalidApiResponse
     */
    public function addPaymentInfoToEmail(string $orderId, $reference)
    {
        $updatedOrder = new \stdClass();
        $updatedOrder->status = 4;
        $updatedOrder->note = '<h4>Payment Information</h4><p>Please use the following information for the banktransfer:</p><ul><li>Reference: ' . $reference . '</li><li>IBAN: NL79ABNA0842577610</li><li>BIC: ABNANL2A</li><li>Account holder: THIRD PARY FUNDS</li><li>City: Amsterdam</li></ul>';
        $updatedOrder->mail = true;


        $this->makeWebRequest('orders/' . $orderId, 'PATCH', $updatedOrder);
    }
}

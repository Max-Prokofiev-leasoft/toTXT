<?php

namespace GingerPayments\Payments\Helpers;

use GingerPayments\Payments\Builders\OrderBuilder;
use GingerPayments\Payments\PSP\PSPConfig;
use OxidEsales\EshopCommunity\Application\Model\Order as OxidOrder;
use GingerPluginSdk\Exceptions\APIException;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;

/**
 * Class PaymentHelper
 * Provides helper functions for processing payments through the Ginger Payments API.
 */
class PaymentHelper
{
    protected GingerApiHelper $gingerApiHelper;

    private static ?PaymentHelper $instance = null;

    /**
     * Constructor to initialize GingerApiHelper.
     */
    private function __construct()
    {
        $this->gingerApiHelper = GingerApiHelper::getInstance();
    }

    /**
     * Retrieves the single instance of this class.
     *
     * @return PaymentHelper
     */
    public static function getInstance(): PaymentHelper
    {
        if (self::$instance === null) {
            self::$instance = new PaymentHelper();
        }
        return self::$instance;
    }

    /**
     * Processes the payment for a given order and return URL on API.
     *
     * @param float $totalAmount
     * Total amount from the OXID order
     * @param OxidOrder $order
     * OXID order
     * @param string $paymentMethod
     * Payment method name
     * @return string
     * - URL to process payment
     * @throws APIException
     */
    public function processPayment(float $totalAmount, OxidOrder $order, string $paymentMethod): string
    {
        $returnUrl = $this->getReturnUrl();
        $webhookUrl = $this->getWebhookUrl($order->getId());
        $orderSdk = new OrderBuilder(
            totalAmount: $totalAmount,
            order: $order,
            paymentMethod: $paymentMethod,
            returnUrl: $returnUrl,
            webhookUrl: $webhookUrl
        );
        return $this->gingerApiHelper->sendOrder(order: $orderSdk->buildOrder())->getPaymentUrl();
    }

    /**
     * Checks if the given payment method is a custom API payment method.
     *
     * @param string $paymentId
     * Selected payment method ID from the OXID
     * @return bool
     * - Returns true if the payment method is a custom API payment method, otherwise false.
     */
    public function isGingerPaymentMethod(string $paymentId): bool
    {
        $paymentMethods = [
            'gingerpaymentsideal',
            'gingerpaymentscreditcard'
        ];

        return in_array($paymentId, $paymentMethods, true);
    }

    /**
     * Maps OXID payment ID to Ginger Plugin payment method name.
     *
     * @param string $paymentId
     * Payment ID from OXID
     * @return string
     * - Valid payment name if it's a Payment Method from Ginger Plugin
     */
    public function mapPaymentMethod(string $paymentId): string
    {
        return match ($paymentId) {
            'gingerpaymentscreditcard' => 'credit-card',
            'gingerpaymentsideal' => 'ideal',
            default => $paymentId,
        };
    }

    /**
     * Maps the Ginger API status to the OXID order status.
     * @param string $apiStatus
     * Status from Ginger API
     * @return string
     * - Mapped Oxid order status
     */
    public function mapApiStatus(string $apiStatus): string
    {
        return match ($apiStatus) {
            'completed' => 'PAID',
            'processing' => 'PROCESSING',
            'cancelled' => 'CANCELLED',
            'expired' => 'EXPIRED',
            default => 'PENDING',
        };
    }

    /**
     * Retrieves the return URL for the SDK Order.
     *
     * @return string
     * - URL to thank you page
     */
    private function getReturnUrl(): string
    {
        $shopUrl = $this->getShopUrl();
        $sessionId = Registry::getSession()->getId();
        return $shopUrl . 'index.php?cl=thankyou&sid=' . $sessionId;
    }

    /**
     * Retrieves the shop URL.
     *
     * @return string
     * - Shop URL
     */
    private function getShopUrl(): string
    {
        return Registry::getConfig()->getShopUrl();
    }

    /**
     * Retrieves the webhook URL for SDK Order.
     *
     * @param string $orderId
     * OXID Order ID
     * @return string
     * - Webhook URL
     */
    private function getWebhookUrl(string $orderId): string
    {
        $shopUrl = "https://e85f-2a02-2378-1082-2fc6-4c6b-89c6-462e-c8cb.ngrok-free.app" . "/";
        return $shopUrl . "widget.php/?cl=webhook&ox_order=" . $orderId;
    }
}
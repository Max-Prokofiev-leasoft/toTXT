<?php

namespace GingerPayments\Payments\Helpers;

use Exception;
use GingerPayments\Payments\Builders\OrderBuilder;
use GingerPayments\Payments\Component\StrategyComponentRegister;
use GingerPayments\Payments\Interfaces\StrategyInterface\ExampleGetWebhookUrlStrategy;
use GingerPayments\Payments\PSP\PSPConfig;
use OxidEsales\Eshop\Core\Exception\LanguageNotFoundException;
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
        try {
          return $this->gingerApiHelper->sendOrder(order: $orderSdk->buildOrder())->getPaymentUrl();
        } catch (Exception $e)
        {
            $this->getGingerError();
            Registry::getLogger()->error("Error message: $e");
        }
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
            'gingerpaymentscreditcard',
            'gingerpaymentsgooglepay',
            'gingerpaymentsapplepay',
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
            'gingerpaymentsgooglepay' => 'google-pay',
            'gingerpaymentsapplepay' => 'apple-pay',
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
        $status = $this->getShopOrderStatus();
        return match ($apiStatus) {
            'completed' => $status['paid'],
            'processing' => $status['processing'],
            'cancelled' => $status['cancelled'],
            'expired' => $status['expired'],
            default => $status['pending'],
        };
    }

    /**
     * Retrieves the shop order statuses from the module settings.
     *
     * This method uses the ModuleSettingServiceInterface to fetch various
     * order statuses (pending, processing, cancelled, expired, and paid)
     * for the 'gingerpayments' module.
     *
     * @return array
     * - An associative array of shop order statuses with keys
     * 'pending', 'processing', 'cancelled', 'expired', and 'paid', each
     * mapped to their respective status values as strings.
     **/
    public function getShopOrderStatus(): array
    {
        $moduleSettingService = ContainerFacade::get(ModuleSettingServiceInterface::class);
        $pendingStatus = $moduleSettingService->getString('gingerpayments_pending_status', 'gingerpayments')->toString();
        $processingStatus = $moduleSettingService->getString('gingerpayments_processing_status', 'gingerpayments')->toString();
        $cancelledStatus = $moduleSettingService->getString('gingerpayments_cancelled_status','gingerpayments')->toString();
        $expiredStatus = $moduleSettingService->getString('gingerpayments_expired_status', 'gingerpayments')->toString();
        $paidStatus = $moduleSettingService->getString('gingerpayments_completed_status', 'gingerpayments')->toString();
        return [
            'pending' => $pendingStatus,
            'processing' => $processingStatus,
            'cancelled' => $cancelledStatus,
            'expired' => $expiredStatus,
            'paid' => $paidStatus,
        ];
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
     * Handle Ginger payment error.
     *
     * This method clears the current session and redirects to the failed payment page.
     * It is used to handle scenarios where the Ginger payment process fails.
     *
     * @return void
     */
    private function getGingerError(): void
    {
        Registry::getSession()->destroy();
        Registry::getUtils()->redirect('widget.php?cl=failedpayment');

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
        $shopUrl = "https://274b-193-109-145-122.ngrok-free.app" . "/";
        return $shopUrl . "widget.php/?cl=webhook&ox_order=" . $orderId;
    }
}
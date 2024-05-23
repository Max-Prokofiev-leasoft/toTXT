<?php

namespace GingerPayments\Payments\Helpers;

use GingerPayments\Payments\Builders\OrderBuilder;
use GingerPayments\Payments\PSP\PSPConfig;
use OxidEsales\EshopCommunity\Application\Model\Order as OxidOrder;
use GingerPluginSdk\Exceptions\APIException;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;

class PaymentHelper
{
    protected GingerApiHelper $gingerApiHelper;

    /**
     * @throws APIException
     */
    public function __construct()
    {
        $this->gingerApiHelper = new GingerApiHelper(endpoint: $this->getEndpoint(), apiKey: $this->getApiKey());
    }

    /**
     * @param float $totalAmount
     * @param OxidOrder $order
     * @param string $paymentMethod
     * @return string
     * @throws APIException
     */
    public function processPayment(float $totalAmount, OxidOrder $order, string $paymentMethod): string
    {
        $returnUrl = $this->getReturnUrl();
        $orderSdk = OrderBuilder::buildOrder(
            totalAmount: $totalAmount,
            order: $order,
            paymentMethod: $paymentMethod,
            returnUrl: $returnUrl
        );
        return $this->gingerApiHelper->sendOrder(order: $orderSdk)->getPaymentUrl();
    }

    public function getApiKey(): string
    {
        $moduleSettingService = ContainerFacade::get(ModuleSettingServiceInterface::class);
        $apiKey = $moduleSettingService->getString('gingerpayment_apikey', 'gingerpayments')->toString();

        if (!$this->isValidApiKeyFormat($apiKey)) {
            throw new \InvalidArgumentException('Invalid API key format.');
        }
        return $apiKey;
    }

    public function getEndpoint(): string
    {
        return PSPConfig::ENDPOINT;
    }

    /**
     * @param string $apiKey
     * @return bool
     */
    private function isValidApiKeyFormat(string $apiKey): bool
    {
        // Ensure API key is alphanumeric and doesn't contain SQL or JavaScript injection patterns
        return ctype_alnum($apiKey) &&
            !preg_match('/[\'";--]|(\/\*)|(\*\/)|(\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|JOIN|CREATE|ALTER|TRUNCATE|REPLACE)\b)/i', $apiKey) &&
            !preg_match('/<script|<\/script>|javascript:/i', $apiKey);
    }

    private function getReturnUrl(): string
    {
        $shopUrl = $this->getShopUrl();
        $sessionId = Registry::getSession()->getId();
        return $shopUrl . 'index.php?cl=thankyou&sid=' . $sessionId;
    }

    private function getShopUrl(): string
    {
        return Registry::getConfig()->getShopUrl();
    }
}

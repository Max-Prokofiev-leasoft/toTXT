<?php

namespace GingerPayments\Payments\Helpers;

use GingerPayments\Payments\PSP\PSPConfig;
use GingerPluginSdk\Client;
use GingerPluginSdk\Entities\Client as ClientEntity;
use GingerPluginSdk\Exceptions\CaptureFailedException;
use GingerPluginSdk\Exceptions\InvalidOrderStatusException;
use GingerPluginSdk\Properties\ClientOptions;
use GingerPluginSdk\Entities\Order;
use GingerPluginSdk\Exceptions\APIException;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;

/**
 * Class GingerApiHelper
 * Provides helper functions for interacting with the Ginger Payments API.
 */
class GingerApiHelper
{
    private static ?GingerApiHelper $instance = null;
    public Client $client;

    /**
     * Private constructor to prevent creating a new instance of the class via the `new` operator from outside of this class.
     *
     * @throws APIException
     */
    private function __construct()
    {
        try {
            $clientOptions = new ClientOptions(endpoint: PSPConfig::getEndpoint(), useBundle: $this->isCacertCheck(), apiKey: $this->getApiKey());
            $this->client = new Client(options: $clientOptions);
        } catch (\Exception $e) {
            throw new APIException(message: "Failed to initialize API client: " . $e->getMessage(), code: $e->getCode(), previous: $e);
        }
    }

    /**
     * Retrieves the single instance of this class.
     *
     * @return GingerApiHelper
     */
    public static function getInstance(): GingerApiHelper
    {
        if (self::$instance === null) {
            self::$instance = new GingerApiHelper();
        }

        return self::$instance;
    }

    /**
     * Sends an order to the Ginger Payments API.
     *
     * @param Order $order
     * SDK Order
     * @return Order
     * @throws APIException
     */
    public function sendOrder(Order $order): Order
    {
        try {
            return $this->client->sendOrder($order);
        } catch (APIException $e) {
            throw new APIException("Error sending order: " . $e->getMessage());
        }
    }

    /**
     * Capture the transaction for a given order.
     *
     * @param string $orderId SDK Order ID of the order to capture.
     * @return bool True if the transaction was captured successfully, otherwise false.
     * @throws APIException If there is an error with the API during the capture process.
     * @throws CaptureFailedException If the capture process fails for any reason.
     * @throws InvalidOrderStatusException If the order status is invalid for capture.
     */
    public function captureTransaction(string $orderId): bool
    {
        try {
            return $this->client->captureOrderTransaction($orderId);
        } catch (APIException $e) {
            throw new APIException("Error capturing transaction: ". $e->getMessage());
        }
    }

    /**
     * Validates the format of the API key to ensure it is safe and correct.
     *
     * @param string $apiKey
     * @return bool
     */
    private function isValidApiKeyFormat(string $apiKey): bool
    {
        // Ensure API key is alphanumeric and doesn't contain SQL or JavaScript injection patterns
        return ctype_alnum($apiKey) &&
            !preg_match('/[\'";\-\-]|(\/\*)|(\*\/)|(\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|JOIN|CREATE|ALTER|TRUNCATE|REPLACE)\b)/i', $apiKey) &&
            !preg_match('/<script|<\/script>|javascript:/i', $apiKey);
    }

    /**
     *  Checks if the CACert setting is enabled.
     *
     *  This method retrieves the boolean value of the 'gingerpayments_cacert' setting from the module settings.
     * @return bool
     * - True if the CACert setting is enabled, false otherwise.
     */
    private function isCacertCheck(): bool
    {
        $moduleSettingService = ContainerFacade::get(ModuleSettingServiceInterface::class);
        return $moduleSettingService->getBoolean('gingerpayments_cacert', 'gingerpayments');

    }

    /**
     * Check if the transaction for a given order is capturable.
     *
     * @param string $orderId SDK Order ID of the order to capture.
     * @return bool True if the transaction is capturable, otherwise false.
     * @throws \Exception If there is an error while retrieving the order.
     */
    public function isCapturable(string $orderId): bool
    {
        $order = $this->client->getOrder($orderId);
        return $order->getCurrentTransaction()->isCapturable();
    }

    /**
     * Retrieves and validates the API key from the module settings.
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getApiKey(): string
    {
        $moduleSettingService = ContainerFacade::get(ModuleSettingServiceInterface::class);
        $apiKey = $moduleSettingService->getString('gingerpayments_apikey', 'gingerpayments')->toString();

        if (!$this->isValidApiKeyFormat(apiKey: $apiKey)) {
            throw new \InvalidArgumentException('Invalid API key format.');
        }
        return $apiKey;
    }


    /**
     * Retrieves an order from the Ginger API by order ID.
     *
     * @param string $orderId
     * SDK Order ID
     * @return Order
     * - SDK Order
     * @throws \Exception
     */
    public function getOrder(string $orderId): Order
    {
        return $this->client->getOrder($orderId);
    }

    /**
     * Retrieves the extra info from the client for Ginger API.
     *
     * @return ClientEntity
     * @throws \InvalidArgumentException
     */
    public function getClientExtra(): ClientEntity
    {
        return new ClientEntity(
            userAgent: PSPConfig::getUserAgent(),
            platformName: PSPConfig::getPlatformName(),
            platformVersion: PSPConfig::getPlatformVersion(),
            pluginName: PSPConfig::getPluginName(),
            pluginVersion: PSPConfig::getPluginVersion()
        );
    }
}

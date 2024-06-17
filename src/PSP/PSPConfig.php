<?php

namespace GingerPayments\Payments\PSP;
use GingerPayments\Payments\Component\StrategyComponentRegister;
use GingerPayments\Payments\Interfaces\StrategyInterface\ExampleGetWebhookUrlStrategy;
use GingerPayments\Payments\Strategies\ExampleGetWebhookUrl;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;

/**
 * Class PSPConfig
 * Defines configuration constants for the Ginger Payments module.
 */
class PSPConfig
{

    public const PLATFORM_NAME = 'OXID E-SHOP';
    public const PLATFORM_VERSION = '7.1';
    public const PLUGIN_NAME = 'OXID Payment';
    public const PLUGIN_VERSION = '1.0.0';
    public const ENDPOINT = 'https://api.dev.gingerpayments.com';
    public const AUTOLOAD_FILE = __DIR__ . '/../../vendor/autoload.php';

    /**
     * Retrieves the endpoint URL for the Ginger Payments API.
     *
     * @return string
     */
    public static function getEndpoint(): string
    {
        return self::ENDPOINT;
    }

    /**
     * Retrieves the platform name for the Ginger Payments API.
     *
     * @return string
     */
    public static function getPlatformName(): string
    {
        return self::PLATFORM_NAME;
    }

    /**
     * Retrieves the platform version for the Ginger Payments API.
     *
     * @return string
     */
    public static function getPlatformVersion(): string
    {
        return self::PLATFORM_VERSION;
    }

    /**
     * Retrieves the plugin name for the Ginger Payments API.
     *
     * @return string
     */
    public static function getPluginName(): string
    {
        return self::PLUGIN_NAME;
    }

    /**
     * Retrieves the plugin version for the Ginger Payments API.
     *
     * @return string
     */
    public static function getPluginVersion(): string
    {
        return self::PLUGIN_VERSION;
    }

    /**
     * Retrieves the user agent from the server.
     *
     * @return string
     */
    public static function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * Register all strategy components.
     * @return void
     */
    public static function registerStrategies(): void
    {
        StrategyComponentRegister::register(
            key: ExampleGetWebhookUrlStrategy::class,
            component: new ExampleGetWebhookUrl()
        );
    }

}
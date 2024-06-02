<?php

namespace GingerPayments\Payments\PSP;
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


}
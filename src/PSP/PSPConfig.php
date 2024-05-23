<?php

namespace GingerPayments\Payments\PSP;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;

class PSPConfig
{

    public const PLATFORM_NAME = 'OXID E-SHOP 7.1';

    public const PLUGIN_NAME = 'OXID Payment';

    public const ENDPOINT = 'https://api.dev.gingerpayments.com';
    public const AUTOLOAD_FILE = __DIR__ . '/../../vendor/autoload.php';


}
<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Core;

use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ShopConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Bridge\ModuleActivationBridgeInterface;
use stdClass;

/**
 * Performs Online Module Version Notifier check.
 *
 * The Online Module Version Notification is used for checking if newer versions of modules are available.
 * Will be used by the upcoming online one click installer.
 * Is still under development - still changes at the remote server are necessary - therefore ignoring the results for now
 *
 * @internal Do not make a module extension for this class.
 *
 * @ignore   This class will not be included in documentation.
 */
class OnlineModuleVersionNotifier
{
    /** @var \OxidEsales\Eshop\Core\OnlineModuleVersionNotifierCaller */
    private $_oCaller;

    public function __construct(\OxidEsales\Eshop\Core\OnlineModuleVersionNotifierCaller $oCaller)
    {
        $this->_oCaller = $oCaller;
    }

    /**
     * Perform Online Module version Notification. Returns result
     *
     * @return null
     */
    public function versionNotify()
    {
        if (true === \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('preventModuleVersionNotify')) {
            return;
        }

        $oOMNCaller = $this->getOnlineModuleNotifierCaller();
        $oOMNCaller->doRequest($this->formRequest());
    }

    /**
     * Collects only required modules information and returns as array.
     *
     * @return null
     */
    protected function prepareModulesInformation()
    {
        $preparedModules = [];

        $shopConfiguration = ContainerFacade::get(ShopConfigurationDaoBridgeInterface::class)
            ->get();
        $moduleActivationBridge = ContainerFacade::get(ModuleActivationBridgeInterface::class);

        foreach ($shopConfiguration->getModuleConfigurations() as $moduleConfiguration) {
            /** @var \OxidEsales\Eshop\Core\Module\Module $oModule */

            $preparedModule = new stdClass();
            $preparedModule->id = $moduleConfiguration->getId();
            $preparedModule->version = $moduleConfiguration->getVersion();

            $preparedModule->activeInShops = new stdClass();
            $preparedModule->activeInShops->activeInShop = [];
            if ($moduleActivationBridge->isActive($moduleConfiguration->getId(), Registry::getConfig()->getShopId())) {
                $preparedModule
                    ->activeInShops
                    ->activeInShop[] = \OxidEsales\Eshop\Core\Registry::getConfig()->getShopUrl();
            }
            $preparedModules[] = $preparedModule;
        }

        return $preparedModules;
    }

    /**
     * Send request message to Online Module Version Notifier web service.
     *
     * @return \OxidEsales\Eshop\Core\OnlineModulesNotifierRequest
     */
    protected function formRequest()
    {
        $oRequestParams = new \OxidEsales\Eshop\Core\OnlineModulesNotifierRequest();

        $oRequestParams->modules = new stdClass();
        $oRequestParams->modules->module = $this->prepareModulesInformation();

        return $oRequestParams;
    }

    /**
     * Returns caller.
     *
     * @return \OxidEsales\Eshop\Core\OnlineModuleVersionNotifierCaller
     */
    protected function getOnlineModuleNotifierCaller()
    {
        return $this->_oCaller;
    }
}

<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao;

use Symfony\Contracts\EventDispatcher\Event;

class ShopEnvironmentWithOrphanSettingEvent extends Event
{
    public function __construct(
        /** @var int */
        private $shopId,
        /** @var  string */
        private $moduleId,
        /** @var string */
        private $settingId
    ) {
    }

    /** @return int */
    public function getShopId(): int
    {
        return $this->shopId;
    }

    /** @return string */
    public function getModuleId(): string
    {
        return $this->moduleId;
    }

    /** @return string */
    public function getSettingId(): string
    {
        return $this->settingId;
    }
}

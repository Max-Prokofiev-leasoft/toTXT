<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ShopEnvironmentMisconfigurationEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /** @param ShopEnvironmentWithOrphanSettingEvent $event */
    public function logOrphanSetting(ShopEnvironmentWithOrphanSettingEvent $event): void
    {
        $this->logger->warning(
            'Environment configuration tries to change non-existing module setting. Environment value will be ignored',
            [
                'shopId' => $event->getShopId(),
                'moduleId' => $event->getModuleId(),
                'settingId' => $event->getSettingId(),
            ]
        );
    }

    /** @return string[] */
    public static function getSubscribedEvents(): array
    {
        return [ShopEnvironmentWithOrphanSettingEvent::class => 'logOrphanSetting'];
    }
}

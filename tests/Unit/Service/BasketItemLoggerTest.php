<?php

/**
 * Copyright © Ginger. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace GingerPayments\Payments\Tests\Service;

use GingerPayments\Payments\Service\BasketItemLogger;
use GingerPayments\Payments\Service\ModuleSettings;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

final class BasketItemLoggerTest extends TestCase
{
    private const TEST_PRODUCT_ID = 'itemId';

    public function testLogWhenEnabled(): void
    {
        $psrLoggerMock = $this->createMock(PsrLoggerInterface::class);
        $psrLoggerMock->expects($this->once())
            ->method('info')
            ->with(
                sprintf(BasketItemLogger::MESSAGE, self::TEST_PRODUCT_ID)
            );

        $moduleSettings = $this->createMock(ModuleSettings::class);
        $moduleSettings->expects($this->once())
            ->method('isLoggingEnabled')
            ->willReturn(true);

        $basketItemLogger = new BasketItemLogger($psrLoggerMock, $moduleSettings);
        $basketItemLogger->log(self::TEST_PRODUCT_ID);
    }

    public function testLogWhenDisabled()
    {
        $psrLoggerMock = $this->createMock(PsrLoggerInterface::class);
        $psrLoggerMock->expects($this->never())
            ->method('info');

        $moduleSettings = $this->createMock(ModuleSettings::class);
        $moduleSettings->expects($this->once())
            ->method('isLoggingEnabled')
            ->willReturn(false);

        $basketItemLogger = new BasketItemLogger($psrLoggerMock, $moduleSettings);
        $basketItemLogger->log(self::TEST_PRODUCT_ID);
    }
}

<?php

/**
 * Copyright © Ginger. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace GingerPayments\Payments\Tests\Unit\Service;

use OxidEsales\Eshop\Core\Request as CoreRequest;
use GingerPayments\Payments\Core\Module as ModuleCore;
use GingerPayments\Payments\Service\GreetingMessage;
use GingerPayments\Payments\Service\ModuleSettings;
use PHPUnit\Framework\TestCase;

final class GreetingMessageTest extends TestCase
{
    /**
     * @dataProvider getGreetingDataProvider
     */
    public function testGenericGreetingNoUser(string $mode, string $expected): void
    {
        $service = new GreetingMessage(
            $this->createConfiguredMock(ModuleSettings::class, ['getGreetingMode' => $mode]),
            $this->createStub(CoreRequest::class)
        );

        $this->assertSame($expected, $service->getGreeting());
    }

    public function getGreetingDataProvider(): array
    {
        return [
            [
                'mode' => ModuleSettings::GREETING_MODE_GENERIC,
                'expected' => ModuleCore::DEFAULT_PERSONAL_GREETING_LANGUAGE_CONST
            ],
            [
                'mode' => ModuleSettings::GREETING_MODE_PERSONAL,
                'expected' => ''
            ]
        ];
    }
}

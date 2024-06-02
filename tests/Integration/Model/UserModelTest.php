<?php

/**
 * Copyright © Ginger. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace GingerPayments\Payments\Tests\Integration\Model;

use OxidEsales\Eshop\Application\Model\User as EshopModelUser;
use GingerPayments\Payments\Tests\Integration\IntegrationTestCase;

final class UserModelTest extends IntegrationTestCase
{
    public function testGetPersonalGreetingNotSet(): void
    {
        $user = oxNew(EshopModelUser::class);

        $this->assertEmpty($user->getPersonalGreeting());
    }

    public function testGetPersonalGreeting(): void
    {
        $user = oxNew(EshopModelUser::class);
        $user->setPersonalGreeting('some information about me');

        $this->assertSame('some information about me', $user->getPersonalGreeting());
    }

    public function testNewFieldNotAutomaticallySavedToDatabase(): void
    {
        $user = oxNew(EshopModelUser::class);
        $user->setId('_testuser');
        $user->save();
        $user->setPersonalGreeting('some information about me');
        unset($user); //this object was not saved after last assign

        $user = oxNew(EshopModelUser::class);
        $user->load('_testuser');
        $this->assertEmpty($user->getPersonalGreeting());
        unset($user);
    }

    public function testNewFieldSavedToDatabase(): void
    {
        $user = oxNew(EshopModelUser::class);
        $user->setId('_newuser');
        $user->save();
        $user->setPersonalGreeting('some information about me');
        $user->save();
        unset($user);

        $user = oxNew(EshopModelUser::class);
        $user->load('_newuser');
        $this->assertTrue($user->isLoaded());
        $this->assertSame('some information about me', $user->getPersonalGreeting());
    }
}

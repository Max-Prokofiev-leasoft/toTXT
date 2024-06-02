<?php

/**
 * Copyright © Ginger. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace GingerPayments\Payments\Tests\Codeception\Helper;

use OxidEsales\Codeception\Module\Translation\Translator;
use GingerPayments\Payments\Core\Module;
use GingerPayments\Payments\Tests\Codeception\AcceptanceTester;

/**
 * @group modules_GingerPayments-oxid
 * @group modules_GingerPayments-oxid_module
 */
final class ModuleCest
{
    public function testCanDeactivateModule(AcceptanceTester $I): void
    {
        $I->wantToTest('that deactivating the module does not destroy the shop');

        $I->openShop();
        $I->waitForText(Translator::translate('OEMODULETEMPLATE_GREETING'));

        $I->deactivateModule(Module::MODULE_ID);
        $I->reloadPage();

        $I->waitForPageLoad();
        $I->dontSee(Translator::translate('OEMODULETEMPLATE_GREETING'));
    }
}

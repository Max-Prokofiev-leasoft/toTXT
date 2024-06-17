<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Codeception\Acceptance\Admin;

use Codeception\Attribute\Group;
use OxidEsales\EshopCommunity\Tests\Codeception\Support\AcceptanceTester;

#[Group('admin')]
final class ExportNewsletterRecipientsCest
{
    public function checkExportRecipients(AcceptanceTester $I): void
    {
        $I->wantToTest('Check Export Newsletter Recipients');

        $adminPanel = $I->loginAdmin();
        $newsletter = $adminPanel->openNewsletter();
        $newsletter->exportReciepents();
        $I->waitForAjax();
    }
}

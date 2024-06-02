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
final class ServiceDiagnosticToolCest
{
    /**
     * @group diagnostic-tool
     */
    public function functionalityDiagnosticTools(AcceptanceTester $I): void
    {
        $I->wantToTest('functionality of diagnostic tools');

        $adminPanel = $I->loginAdmin();

        $diagToolPanel = $adminPanel->openDiagnosticsTool();
        $diagToolPanel->startDiagnostics();
        $diagToolPanel->seeDiagnosticResults();
    }
}

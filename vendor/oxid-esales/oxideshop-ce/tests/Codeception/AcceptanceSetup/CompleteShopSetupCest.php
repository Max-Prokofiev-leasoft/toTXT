<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Codeception\AcceptanceSetup;

use Codeception\Attribute\Group;
use OxidEsales\Codeception\ShopSetup\DataObject\UserInput;
use OxidEsales\EshopCommunity\Tests\Codeception\Support\AcceptanceSetupTester;
use OxidEsales\EshopCommunity\Tests\Codeception\Support\AcceptanceTester;

#[Group('setup', 'exclude_from_compilation')]
final class CompleteShopSetupCest
{
    private UserInput $userInput;

    public function _before(AcceptanceSetupTester $I): void
    {
        $this->userInput = $I->getDataForUserInput();
    }

    public function testInstallShopWithoutDemoData(
        AcceptanceSetupTester $I,
        AcceptanceTester $IShop,
    ): void {
        if (!$I->isCommunityEdition()) {
            $I->markTestSkipped('This test is for Community edition only.');
        }
        $I->wantToTest('full setup flow without demo data.');

        $adminUserName = 'test-user@login.email';
        $adminUserPassword = 'test123';

        $I->amGoingTo('go through the shop setup steps');
        $finishStep = $I
            ->openShopSetup()
            ->selectInstallationLanguage('English')
            ->proceedToWelcomeStep()
            ->selectDeliveryCountry('Germany')
            ->selectShopLanguage('English')
            ->selectCheckForUpdates()
            ->proceedToLicenseAndConditionsStep()
            ->proceedToDatabaseStep()
            ->fillDatabaseConnectionFields($this->userInput)
            ->selectSetupWithoutDemodata()
            ->proceedToDirectoryAndLoginStep()
            ->fillAdminCredentials(
                $adminUserName,
                $adminUserPassword,
                $adminUserPassword
            )
            ->proceedToFinishStep();

        $I->amGoingTo('see the maintenance mode because of the inactive theme.');
        $homePage = $finishStep->openShop($IShop);
        $I->switchToNextTab();
        $homePage->isInMaintenanceMode();
        $I->closeTab();

        $I->amGoingTo('activate a shop theme.');
        $I->activateTheme($this->userInput->getThemeId());

        $I->expectTo('see the shop is now usable');
        $homePage = $finishStep->openShop($IShop);
        $I->switchToNextTab();
        $homePage->openUserAccountPage();
        $I->closeTab();

        $I->expectTo('see the admin is usable with provided credentials');
        $adminPage = $finishStep->openAdmin($IShop);
        $I->switchToNextTab();
        $adminPanel = $adminPage->login($adminUserName, $adminUserPassword);
        $adminPanel->openCoreSettings();
        $I->closeTab();
    }
}

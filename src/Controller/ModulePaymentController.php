<?php

namespace GingerPayments\Payments\Controller;

use GingerPayments\Payments\Helpers\GingerApiHelper;
use GingerPayments\Payments\Helpers\PaymentHelper;
use GingerPayments\Payments\PSP\PSPConfig;
use GingerPluginSdk\Properties\Currency;
use OxidEsales\Eshop\Application\Controller\PaymentController;
use OxidEsales\EshopCommunity\Core\Registry;
use OxidEsales\EshopCommunity\Application\Model\DeliverySetList;

/**
 * Class ModulePaymentController
 * Extends the default OXID PaymentController to integrate Ginger Payments API functionality.
 */
class ModulePaymentController extends PaymentController
{
    private GingerApiHelper $gingerApiHelper;
    private PaymentHelper $paymentHelper;

    /**
     * Constructor to initialize GingerApiHelper.
     */
    public function __construct()
    {
        parent::__construct();
        require_once PSPConfig::AUTOLOAD_FILE;
        $this->gingerApiHelper = GingerApiHelper::getInstance();
        $this->paymentHelper = new PaymentHelper();
    }

    /**
     * Initializes the controller.
     * Calls the parent init method.
     *
     * @return void
     */
    public function init(): void
    {
        parent::init();
    }

    /**
     * Retrieves and returns the list of available payment methods.
     *
     * This method checks if the payment list is already set. If not, it attempts to retrieve the active shipping set from the request parameters or session.
     * Then, it gets the current basket and the delivery set data including all available sets, the active shipping set, and the payment list.
     * The shipping method for the basket is set, and each payment method is checked for availability using the specified currency.
     * Finally, it calculates the payment expenses for preview and sets the payment list.
     *
     * @return array|null
     * - Returns the list of available payment methods or null if none are available.
     */
    public function getPaymentList(): mixed
    {
        if ($this->_oPaymentList === null) {
            $this->_oPaymentList = false;

            $sActShipSet = Registry::getRequest()->getRequestEscapedParameter('sShipSet');
            if (!$sActShipSet) {
                $sActShipSet = Registry::getSession()->getVariable('sShipSet');
            }

            $session = Registry::getSession();
            $oBasket = $session->getBasket();

            list($aAllSets, $sActShipSet, $aPaymentList) =
                Registry::get(DeliverySetList::class)->getDeliverySetData($sActShipSet, $this->getUser(), $oBasket);

            $oBasket->setShipping($sActShipSet);

            $shopCurrency = Registry::getConfig()->getActShopCurrencyObject()->name;
            $currency = new Currency(value: $shopCurrency);

            foreach ($aPaymentList as $paymentId => $payment) {
                $mappedPaymentMethod = $this->paymentHelper->mapPaymentMethod(paymentId: $paymentId);
                if (($mappedPaymentMethod !== $paymentId) && !$this->gingerApiHelper->client->checkAvailabilityForPaymentMethodUsingCurrency(payment_method_name: $mappedPaymentMethod, currency: $currency)) {
                    unset($aPaymentList[$paymentId]);
                }
            }

            $this->setValues($aPaymentList, $oBasket);
            $this->_oPaymentList = $aPaymentList;
            $this->_aAllSets = $aAllSets;
        }

        return $this->_oPaymentList;
    }

}

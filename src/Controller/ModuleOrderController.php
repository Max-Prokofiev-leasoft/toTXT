<?php

namespace GingerPayments\Payments\Controller;

use GingerPayments\Payments\Helpers\GingerApiHelper;
use GingerPayments\Payments\Helpers\PaymentHelper;
use GingerPayments\Payments\PSP\PSPConfig;
use OxidEsales\Eshop\Application\Controller\OrderController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;
use OxidEsales\Eshop\Core\Exception\OutOfStockException;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class ModuleOrderController
 * Extends the default OXID OrderController to integrate Ginger Payments API functionality.
 */
class ModuleOrderController extends OrderController
{
    private PaymentHelper $paymentHelper;

    /**
     * Constructor to initialize GingerApiHelper.
     */
    public function __construct()
    {
        parent::__construct();
        require_once PSPConfig::AUTOLOAD_FILE;
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
     * Executes the order process and handles API redirect logic if the payment method matches.
     *
     * @return string|null
     * - Returns the next step if successful, 'user' if no user, or null if an error occurs.
     */
    public function execute(): ?string
    {
        $session = Registry::getSession();
        if (!$session->checkSessionChallenge()) {
            return null;
        }

        if (!$this->validateTermsAndConditions()) {
            $this->_blConfirmAGBError = 1;
            return null;
        }

        $user = $this->getUser();
        if (!$user) {
            return 'user';
        }

        // get basket contents
        $basket = $session->getBasket();
        if ($basket->getProductsCount()) {
            try {
                $order = oxNew(Order::class);
                $iSuccess = $order->finalizeOrder($basket, $user);
                $user->onOrderExecute($basket, $iSuccess);

                if ($iSuccess === Order::ORDER_STATE_OK && $this->paymentHelper->isGingerPaymentMethod(paymentId: $order->oxorder__oxpaymenttype->value)) {
                    $apiUrl = $session->getVariable('payment_url');
                    Registry::getUtils()->redirect($apiUrl, true, 302);
                }
                Registry::getLogger()->error('Not Redirected');
                return $this->getNextStep($iSuccess);
            } catch (OutOfStockException $oEx) {
                $oEx->setDestination('basket');
                Registry::getUtilsView()->addErrorToDisplay($oEx, false, true, 'basket');
            } catch (NoArticleException $oEx) {
                Registry::getUtilsView()->addErrorToDisplay($oEx);
            } catch (ArticleInputException $oEx) {
                Registry::getUtilsView()->addErrorToDisplay($oEx);
            }
        }
        return null;
    }


}

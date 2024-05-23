<?php

namespace GingerPayments\Payments\Controller;

use OxidEsales\Eshop\Application\Controller\OrderController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;
use OxidEsales\Eshop\Core\Exception\OutOfStockException;
use OxidEsales\Eshop\Core\Registry;

class ModuleOrderController extends OrderController
{
    public function init()
    {
        parent::init();
    }
    /**
     * Executes parent::execute(), adds API redirect logic if payment method matches.
     *
     * @return string|null
     */
    public function execute()
    {
        $session = Registry::getSession();
        if (!$session->checkSessionChallenge()) {
            return;
        }

        if (!$this->validateTermsAndConditions()) {
            $this->_blConfirmAGBError = 1;

            return;
        }

        // additional check if we really really have a user now
        $user = $this->getUser();
        if (!$user) {
            return 'user';
        }

        // get basket contents
        $basket = $session->getBasket();
        if ($basket->getProductsCount()) {
            try {
                $order = oxNew(Order::class);

                //finalizing ordering process (validating, storing order into DB, executing payment, setting status ...)
                $iSuccess = $order->finalizeOrder($basket, $user);

                // performing special actions after user finishes order (assignment to special user groups)
                $user->onOrderExecute($basket, $iSuccess);

                if ($iSuccess === Order::ORDER_STATE_OK && $this->isGingerPaymentMethod($order->oxorder__oxpaymenttype->value)) {
                    $apiUrl = $session->getVariable('payment_url');
                    Registry::getUtils()->redirect($apiUrl, true, 302);
                }
                // proceeding to next view
                Registry::getLogger()->error('yes');
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
    }

    /**
     * Check if the payment method is a custom API payment method
     *
     * @param string $paymentType
     * @return bool
     */
    private function isGingerPaymentMethod(string $paymentType): bool
    {
        $paymentMethods = [
            'gingerpaymentsideal',
            'gingerpaymentscreditcard'
        ];

        return in_array($paymentType, $paymentMethods, true);
    }
}

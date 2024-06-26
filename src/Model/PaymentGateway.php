<?php

namespace GingerPayments\Payments\Model;

use GingerPayments\Payments\Component\StrategyComponentRegister;
use GingerPayments\Payments\Helpers\PaymentHelper;
use GingerPayments\Payments\Interfaces\StrategyInterface\ExampleGetWebhookUrlStrategy;
use GingerPayments\Payments\Payments\CreditCardPayment;
use GingerPayments\Payments\Payments\Factory\PaymentFactory;
use GingerPayments\Payments\Payments\IdealPayment;
use GingerPayments\Payments\PSP\PSPConfig;
use GingerPluginSdk\Exceptions\APIException;
use OxidEsales\EshopCommunity\Application\Model\Order as OxidOrder;
use OxidEsales\EshopCommunity\Core\Registry;

/**
 * Class PaymentGateway
 *
 * This class is responsible for handling the execution of payments based on the selected payment method.
 * It supports multiple payment methods and integrates with the Ginger Payments SDK.
 */
class PaymentGateway
{
    private PaymentHelper $paymentHelper;
    /**
     * Initializes the PaymentGateway class.
     *
     * The constructor loads the PSP configuration file, registers strategy components,
     * and initializes the Payment Helper.
     */
    public function __construct()
    {
        require_once PSPConfig::AUTOLOAD_FILE;
        PSPConfig::registerStrategies();
        $this->paymentHelper = PaymentHelper::getInstance();
    }

    private object $paymentInfo;

    /**
     * Sets payment parameters.
     *
     * @param object $userPayment User payment object
     * @return void
     */
    public function setPaymentParams(object $userPayment): void
    {
        $this->paymentInfo = &$userPayment;
    }

    /**
     * Executes payment based on the selected payment method.
     *
     * @param float $amount Payment amount
     * @param OxidOrder $order OXID Order
     * @return bool
     * - True on successful execution
     */
    public function executePayment(float $amount, OxidOrder $order): bool
    {
        $paymentId = @$this->paymentInfo->oxuserpayments__oxpaymentsid->value;

        if ($this->paymentHelper->isGingerPaymentMethod(paymentId: $paymentId))
        {
            $paymentMethod = PaymentFactory::createPayment(paymentId: $paymentId);
            $paymentUrl = $paymentMethod->handlePayment(amount: $amount, order: $order);
            Registry::getSession()->setVariable('payment_url', $paymentUrl);
        }
        return true;
    }
}

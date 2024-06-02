<?php

namespace GingerPayments\Payments\Model;

use GingerPayments\Payments\Helpers\PaymentHelper;
use GingerPayments\Payments\Payments\CreditCardPayment;
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
    private array $paymentMethods;

    /**
     * Initializes the PaymentGateway class.
     *
     * The constructor loads the PSP configuration file and initializes the supported payment methods.
     */
    public function __construct()
    {
        require_once PSPConfig::AUTOLOAD_FILE;
        $this->paymentMethods = [
            'gingerpaymentsideal' => new IdealPayment(),
            'gingerpaymentscreditcard' => new CreditCardPayment()
        ];
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
     * @throws APIException
     */
    public function executePayment(float $amount, OxidOrder $order): bool
    {
        $paymentId = @$this->paymentInfo->oxuserpayments__oxpaymentsid->value;

        if (isset($this->paymentMethods[$paymentId])) {
            $paymentMethod = $this->paymentMethods[$paymentId];
            $paymentUrl = $paymentMethod->handlePayment(amount: $amount, order: $order);
            Registry::getSession()->setVariable('payment_url', $paymentUrl);
        }
        return true;
    }
}

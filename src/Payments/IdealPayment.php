<?php

namespace GingerPayments\Payments\Payments;

use GingerPayments\Payments\Helpers\PaymentHelper;
use GingerPayments\Payments\Interfaces\FactoryInterface\BasePaymentInterface;
use GingerPluginSdk\Exceptions\APIException;
use OxidEsales\Eshop\Core\Exception\LanguageNotFoundException;
use OxidEsales\EshopCommunity\Core\Registry;
use OxidEsales\EshopCommunity\Application\Model\Order as OxidOrder;

/**
 * Class IdealPayment
 * Handles the iDEAL payment process using the Ginger Payments API.
 */
class IdealPayment implements BasePaymentInterface
{
    private PaymentHelper $paymentHelper;

    /**
     * Constructor to initialize PaymentHelper.
     */
    public function __construct()
    {
        $this->paymentHelper = PaymentHelper::getInstance();
    }

    /**
     * Handles the payment process for an iDEAL payment.
     *
     * @param float $amount
     * Total amount for the order
     * @param OxidOrder $order
     * OXID Order
     * @return string
     * - URL to process payment or payment confirmation
     * @throws APIException
     * @throws LanguageNotFoundException
     */
    public function handlePayment(float $amount, OxidOrder $order): string
    {
        return $this->paymentHelper->processPayment(
            totalAmount: $amount,
            order: $order,
            paymentMethod: "ideal",
        );
    }
}
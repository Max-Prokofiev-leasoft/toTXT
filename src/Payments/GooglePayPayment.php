<?php

namespace GingerPayments\Payments\Payments;

use GingerPayments\Payments\Helpers\PaymentHelper;
use GingerPayments\Payments\Interfaces\FactoryInterface\BasePaymentInterface;
use GingerPluginSdk\Exceptions\APIException;
use OxidEsales\Eshop\Core\Exception\LanguageNotFoundException;
use OxidEsales\EshopCommunity\Application\Model\Order as OxidOrder;

class GooglePayPayment implements  BasePaymentInterface
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
     * Handles the payment process for an Google Pay payment.
     *
     * @param float $amount
     * Total amount for the order
     * @param OxidOrder $order
     * OXID Order
     * @return string
     * - URL to process payment or payment confirmation
     */
    public function handlePayment(float $amount, OxidOrder $order): string
    {
        return $this->paymentHelper->processPayment(
            totalAmount: $amount,
            order: $order,
            paymentMethod: "google-pay",
        );
    }
}
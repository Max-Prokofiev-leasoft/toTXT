<?php

namespace GingerPayments\Payments\Payments\Factory;

use GingerPayments\Payments\Interfaces\FactoryInterface\BasePaymentInterface;
use GingerPayments\Payments\Payments\ApplePayPayment;
use GingerPayments\Payments\Payments\CreditCardPayment;
use GingerPayments\Payments\Payments\GooglePayPayment;
use GingerPayments\Payments\Payments\IdealPayment;

class PaymentFactory
{
    public static function createPayment(string $paymentId): BasePaymentInterface
    {
        return match ($paymentId) {
            'gingerpaymentscreditcard' => new CreditCardPayment(),
            'gingerpaymentsideal' => new IdealPayment(),
            'gingerpaymentsgooglepay' => new GooglePayPayment(),
            'gingerpaymentsapplepay' => new ApplePayPayment(),
            default => throw new \InvalidArgumentException("Invalid payment method ID"),
        };
    }

}
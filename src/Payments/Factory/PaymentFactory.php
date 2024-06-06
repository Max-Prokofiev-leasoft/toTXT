<?php

namespace GingerPayments\Payments\Payments\Factory;

use GingerPayments\Payments\Interfaces\BasePaymentInterface;
use GingerPayments\Payments\Payments\CreditCardPayment;
use GingerPayments\Payments\Payments\IdealPayment;

class PaymentFactory
{
    public static function createPayment(string $paymentId): BasePaymentInterface
    {
        return match ($paymentId) {
            'gingerpaymentscreditcard' => new CreditCardPayment(),
            'gingerpaymentsideal' => new IdealPayment(),
            default => throw new \InvalidArgumentException("Invalid payment method ID"),
        };
    }

}
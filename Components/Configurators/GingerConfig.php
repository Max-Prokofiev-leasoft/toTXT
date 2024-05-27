<?php

namespace GingerPlugins\Components\Configurators;

class GingerConfig extends AppConfig
{
    const PLUGIN_VERSION = '1.0.0';

    const GINGER_TO_CCVSHOP_ORDER_STATUS = [
        'cancelled' => 'CANCELLED',
        'expired' => 'EXPIRED',
        'error' => 'FAILED',
        'completed' => 'SUCCESS',
        'processing' => 'OPEN',
        'pending' => 'OPEN',
        'new' => 'OPEN'
    ];

    const CCVSHOP_TO_BANK_PAYMENTS =
        [
            'applepay' => 'apple-pay',
            'klarnapaylater' => 'klarna-pay-later',
            'klarnapaynow' => 'klarna-pay-now',
            'paynow' => null,
            'ideal' => 'ideal',
            'afterpay' => 'afterpay',
            'amex' => 'amex',
            'bancontact' => 'bancontact',
            'banktransfer' => 'bank-transfer',
            'creditcard' => 'credit-card',
            'payconiq' => 'payconiq',
            'paypal' => 'paypal',
            'tikkiepaymentrequest' => 'tikkie-payment-request',
            'wechat' => 'wechat',
            'googlepay' => 'google-pay',
            'klarnadirectdebit' => 'klarna-direct-debit',
            'sofort' => 'sofort'
        ];

    public static function getBankPaymentLabels()
    {
        return BankConfig::GINGER_PAYMENTS_LABELS;
    }

    const PRESALE_PAYMENTS = [
        'ideal',
        'afterpay'
    ];

    const GINGER_PAYMENTS_LABELS = [
        'klarnapaylater' => 'Klarna Pay Later',
        'klarnapaynow' => 'Klarna Pay Now',
        'paynow' => 'Pay Now',
        'applepay' => 'Apple Pay',
        'ideal' => 'iDEAL',
        'afterpay' => 'Afterpay',
        'amex' => 'American Express',
        'bancontact' => 'Bancontact',
        'banktransfer' => 'Bank Transfer',
        'creditcard' => 'Credit Card',
        'paypal' => 'PayPal',
        'payconiq' => 'Payconiq',
        'tikkiepaymentrequest' => 'Tikkie Payment Request',
        'sofort' => 'Sofort',
        'klarnadirectdebit' => 'Klarna Direct Debit',
        'googlepay' => 'Google Pay',
        'wechat' => 'WeChat'
    ];
    const GINGER_IP_VALIDATION_PAYMENTS = [
        'afterpay',
        'klarnapaylater'
    ];
    const GINGER_REQUIRED_IBAN_INFO_PAYMENTS = [
        'bank-transfer'
    ];
    const GINGER_REQUIRED_ORDER_LINES_PAYMENTS = [
        'afterpay',
        'klarnadirectdebit',
        'klarnapaylater'
    ];

    const GINGER_CAPTURE_PAYMENTS = [
        'klarnapaylater',
        'afterpay'
    ];
}
<?php

$sMetadataVersion = '2.1';

$aModule = [
    'id' => 'gingerpayments',
    'title' => [
        'de' => 'Ginger Payments',
        'en' => 'Ginger Payments',
        'fr' => 'Ginger Payments'
    ],
    'description' => [
        'de' => 'Ginger Payments solution DE',
        'en' => 'Ginger Payments solution EN',
        'fr' => 'Ginger Payments solution FR',
        'nl' => 'Ginger Payments solution NL',
    ],
    'thumbnail' => 'pictures/logo.png',
    'version' => '1.0.0',
    'author' => 'Ginger Payments',
    'url' => 'https://merchant.dev.gingerpayments.com/',
    'email' => 'max.prokofiev@leasoft.org',
    'extend' => [
        oxpaymentgateway::class => \GingerPayments\Payments\Model\PaymentGateway::class,
        order::class => GingerPayments\Payments\Controller\ModuleOrderController::class,
        payment::class => GingerPayments\Payments\Controller\ModulePaymentController::class,
    ],
    'controllers' => [
        'webhook' => \GingerPayments\Payments\Component\Widget\WebhookController::class,
        'failedpayment' => \GingerPayments\Payments\Component\Widget\FailedPaymentController::class,
    ],
    'events' => [
        'onActivate' => '\GingerPayments\Payments\Core\ModuleEvents::onActivate',
        'onDeactivate' => '\GingerPayments\Payments\Core\ModuleEvents::onDeactivate'
    ],
    'settings' => [
        [
            'group' => 'gingerpayments_main',
            'name' => 'gingerpayments_main_info',
            'type' => 'text',
            'value' => '',
        ],
        [
            'group' => 'gingerpayments_main',
            'name' => 'gingerpayments_apikey',
            'type' => 'str',
            'value' => '',
        ],
        [
            'group' => 'gingerpayments_main',
            'name' => 'gingerpayments_cacert',
            'type' => 'bool',
            'value' => 'false'
        ],
        [
            'group' => 'gingerpayments_additional',
            'name' => 'gingerpayments_additional_info_text',
            'type' => 'text',
            'value' => ''
        ],
        [
            'group' => 'gingerpayments_additional',
            'name' => 'gingerpayments_pending_status',
            'type' => 'str',
            'value' => 'PENDING'
        ],
        [
            'group' => 'gingerpayments_additional',
            'name' => 'gingerpayments_processing_status',
            'type' => 'str',
            'value' => 'PROCESSING'
        ],
        [
            'group' => 'gingerpayments_additional',
            'name' => 'gingerpayments_completed_status',
            'type' => 'str',
            'value' => 'PAID'
        ],
        [
            'group' => 'gingerpayments_additional',
            'name' => 'gingerpayments_cancelled_status',
            'type' => 'str',
            'value' => 'CANCELLED'
        ],
        [
            'group' => 'gingerpayments_additional',
            'name' => 'gingerpayments_expired_status',
            'type' => 'str',
            'value' => 'EXPIRED'
        ],

    ],
];

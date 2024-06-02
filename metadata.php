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
        \OxidEsales\Eshop\Application\Controller\OrderController::class => GingerPayments\Payments\Controller\ModuleOrderController::class,
        payment::class => GingerPayments\Payments\Controller\ModulePaymentController::class,
    ],
    'controllers' => [
        'webhook' => \GingerPayments\Payments\Component\Widget\WebhookController::class,
    ],
    'events' => [
        'onActivate' => '\GingerPayments\Payments\Core\ModuleEvents::onActivate',
        'onDeactivate' => '\GingerPayments\Payments\Core\ModuleEvents::onDeactivate'
    ],
    'settings' => [
        [
            'group' => 'gingerpayments_main',
            'name' => 'gingerpayments_apikey',
            'type' => 'str',
            'value' => 'Please insert your API key'
        ],
    ],
];

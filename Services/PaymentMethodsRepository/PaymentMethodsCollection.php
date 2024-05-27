<?php

namespace GingerPlugins\Services\PaymentMethodsRepository;

use GingerPlugins\Components\Configurators\BankConfig;
use GingerPlugins\Entities\PaymentMethod;

class PaymentMethodsCollection {

    protected array $payment_collection;

    public function __construct()
    {
        foreach (BankConfig::getBankPaymentLabels() as $code => $label)
        {
            $this->payment_collection[] = new PaymentMethod($code, $label);
        }
    }

    public function getPaymentCollection(): array
    {
        return $this->payment_collection;
    }
}
<?php

namespace GingerPlugins\Entities;

use GingerPlugins\Components\Configurators\BankConfig;

class PaymentMethod
{
    public string $id;
    public string $name;
    public string $icon;
    public string $type;


    public function __construct($id, $label)
    {
        $this->id = $id;
        $this->name = implode(' ', [BankConfig::BANK_PAYMENT_LABEL_PREFIX, $label]);
        $this->icon = implode('/', [
            BankConfig::AppUri,
            BankConfig::IMAGE_FOLDER,
            implode(
                '_', ['ginger', $this->id]
            ) . ".png"
        ]);
        $this->type = in_array($id, BankConfig::PRESALE_PAYMENTS) ? 'presale' : 'postsale';
    }
}
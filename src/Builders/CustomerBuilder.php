<?php

namespace GingerPayments\Payments\Builders;

use GingerPluginSdk\Collections\AdditionalAddresses;
use GingerPluginSdk\Entities\Customer;
use GingerPluginSdk\Entities\Address;
use GingerPluginSdk\Properties\EmailAddress;
use Monolog\Logger;
use Monolog\Registry;
use OxidEsales\EshopCommunity\Application\Model\Order as OxidOrder;

class CustomerBuilder
{
    public static function buildCustomer(OxidOrder $order): Customer
    {
        // Build customer entity from order data
        $user = $order->getUser();
        $address = new Address(
            'billing',
            $user->oxuser__oxzip->value,
            new \GingerPluginSdk\Properties\Country(self::getCountryIso(user: $user))
        );

        return new Customer(
            new AdditionalAddresses(addresses: $address),
            $user->oxuser__oxfname->value,
            $user->oxuser__oxlname->value,
            new EmailAddress(value: $user->oxuser__oxusername->value)
        );
    }

    protected static function getCountryIso(object $user)
    {
        // Get country ISO code from user data
        $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $country->load($user->oxuser__oxcountryid->value);
        return $country->oxcountry__oxisoalpha2->value;
    }
}

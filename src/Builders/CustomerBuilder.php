<?php

namespace GingerPayments\Payments\Builders;

use GingerPluginSdk\Collections\AdditionalAddresses;
use GingerPluginSdk\Collections\PhoneNumbers;
use GingerPluginSdk\Entities\Customer;
use GingerPluginSdk\Entities\Address;
use GingerPluginSdk\Properties\EmailAddress;
use GingerPluginSdk\Properties\Country;
use GingerPluginSdk\Properties\Birthdate;
use GingerPluginSdk\Properties\Locale;
use OxidEsales\EshopCommunity\Application\Model\Order as OxidOrder;
use OxidEsales\EshopCommunity\Core\Registry;

class CustomerBuilder
{
    private OxidOrder $order;

    public function __construct(OxidOrder $order)
    {
        $this->order = $order;
    }

    /**
     * Builds a SDK Customer entity from the given OXID order.
     *
     * @return Customer
     * - SDK Customer
     */
    public function buildCustomer(): Customer
    {
        $user = $this->order->getUser();

        $billingAddress = new Address(
            addressType: 'billing',
            postalCode: $user->oxuser__oxzip->value,
            country: new Country($this->getCountryIso($user)),
            address: $user->oxuser__oxstreet->value . " " . $user->oxuser__oxstreetnr->value . " " . $user->oxuser__oxzip->value . " " . $user->oxuser__oxcity->value,
        );

        $deliveryAddressInfo = $this->order->getDelAddressInfo();
        $addresses = [$billingAddress];

        if ($deliveryAddressInfo) {
            $deliveryAddress = new Address(
                addressType: 'delivery',
                postalCode: $deliveryAddressInfo->oxaddress__oxzip->value,
                country: new Country($this->getCountryIso($deliveryAddressInfo)),
                address: $deliveryAddressInfo->oxaddress__oxstreet->value . " " . $deliveryAddressInfo->oxaddress__oxstreetnr->value . " " . $deliveryAddressInfo->oxaddress__oxzip->value . " " . $deliveryAddressInfo->oxaddress__oxcity->value,
            );
            $addresses[] = $deliveryAddress;
        }
        $additionalAddresses = new AdditionalAddresses(...$addresses);

        $phoneNumbers = new PhoneNumbers();
        if ($user->oxuser__oxfon->value) {
            $phoneNumbers->add($user->oxuser__oxfon->value);
        }
        if ($user->oxuser__oxmobfon->value) {
            $phoneNumbers->add($user->oxuser__oxmobfon->value);
        }

        // Build customer entity
        $customer = new Customer(
            additionalAddresses: $additionalAddresses,
            firstName: $user->oxuser__oxfname->value,
            lastName: $user->oxuser__oxlname->value,
            emailAddress: new EmailAddress($user->oxuser__oxusername->value),
            gender: $this->mapGender($user->oxuser__oxsal->value),
            phoneNumbers: $phoneNumbers ?: null,
            birthdate: $user->oxuser__oxbirthdate->value ? new Birthdate($user->oxuser__oxbirthdate->value) : null,
            country: new Country($this->getCountryIso($user)),
            ipAddress: $_SERVER['REMOTE_ADDR'] ?? null,
            locale: new Locale(Registry::getLang()->getLanguageAbbr()),
            merchantCustomerId: $user->oxuser__oxid->value,
            address: $billingAddress->getAddressLine(),
            addressType: 'customer'
        );

        return $customer;
    }

    /**
     * Retrieves the ISO country code from the given OXID user object.
     *
     * @param object $object
     *  OXID User or Address object
     * @return string
     * - Country ISO from OXID User object
     */
    protected function getCountryIso(object $object): string
    {
        $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $country->load($object->oxaddress__oxcountryid->value ?? $object->oxuser__oxcountryid->value);
        return $country->oxcountry__oxisoalpha2->value;
    }

    /**
     * Maps OXID gender to Ginger Plugin SDK gender.
     *
     * @param string $oxidGender
     * OXID gender value
     * @return string|null
     * - Mapped gender value or null if not applicable
     */
    protected function mapGender(string $oxidGender): ?string
    {
        return match (strtolower($oxidGender)) {
            'mr' => 'male',
            'mrs' => 'female',
            default => null,
        };
    }
}


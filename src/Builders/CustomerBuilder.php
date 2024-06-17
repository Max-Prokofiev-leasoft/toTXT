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
use OxidEsales\Eshop\Core\Exception\LanguageNotFoundException;
use OxidEsales\EshopCommunity\Application\Model\Order as OxidOrder;
use OxidEsales\EshopCommunity\Core\Registry;

class CustomerBuilder
{
    private OxidOrder $order;

    /**
     * CustomerBuilder constructor.
     *
     * @param OxidOrder $order
     *  The OXID order object.
     */
    public function __construct(OxidOrder $order)
    {
        $this->order = $order;
    }

    /**
     * Builds a SDK Customer entity from the given OXID order.
     *
     * @return Customer
     * - SDK Customer
     * @throws LanguageNotFoundException
     */
    public function buildCustomer(): Customer
    {
        $user = $this->order->getUser();
        $address = $this->buildAddress($user, 'billing');

        return new Customer(
            additionalAddresses: $this->buildAdditionalAddresses($address),
            firstName: $user->oxuser__oxfname->value,
            lastName: $user->oxuser__oxlname->value,
            emailAddress: new EmailAddress($user->oxuser__oxusername->value),
            gender: $this->mapGender($user->oxuser__oxsal->value),
            phoneNumbers: $this->buildPhoneNumbers($user) ?: null,
            birthdate: $user->oxuser__oxbirthdate->value ? new Birthdate($user->oxuser__oxbirthdate->value) : null,
            country: new Country($this->getCountryIso($user)),
            ipAddress: $_SERVER['REMOTE_ADDR'] ?? null,
            locale: new Locale(Registry::getLang()->getLanguageAbbr()),
            merchantCustomerId: $user->oxuser__oxid->value,
            address: $address->getAddressLine(),
            addressType: 'customer'
        );
    }

    /**
     * Builds an Address entity from a user or address object.
     *
     * @param object $userOrAddress
     *  The OXID user or address object.
     * @param string $type
     *  The type of address ('billing' or 'delivery').
     * @return Address
     * - SDK Address entity
     */
    private function buildAddress(object $userOrAddress, string $type): Address
    {
        return new Address(
            addressType: $type,
            postalCode: $userOrAddress->oxuser__oxzip->value ?? $userOrAddress->oxaddress__oxzip->value,
            country: new Country($this->getCountryIso($userOrAddress)),
            address: $this->formatAddress($userOrAddress),
        );
    }

    /**
     * Builds AdditionalAddresses collection including billing and possibly delivery addresses.
     *
     * @param Address $billingAddress
     *  The billing address.
     * @return AdditionalAddresses
     * - Collection of additional addresses
     */
    private function buildAdditionalAddresses(Address $billingAddress): AdditionalAddresses
    {
        $addresses = [$billingAddress];
        $deliveryAddressInfo = $this->order->getDelAddressInfo();

        if ($deliveryAddressInfo) {
            $deliveryAddress = $this->buildAddress($deliveryAddressInfo, 'delivery');
            $addresses[] = $deliveryAddress;
        }

        return new AdditionalAddresses(...$addresses);
    }

    /**
     * Builds a PhoneNumbers collection from the user object.
     *
     * @param object $user
     *  The OXID user object.
     * @return PhoneNumbers
     * - Collection of phone numbers
     */
    private function buildPhoneNumbers(object $user): PhoneNumbers
    {
        $phoneNumbers = new PhoneNumbers();

        if ($user->oxuser__oxfon->value) {
            $phoneNumbers->add($user->oxuser__oxfon->value);
        }

        if ($user->oxuser__oxmobfon->value) {
            $phoneNumbers->add($user->oxuser__oxmobfon->value);
        }

        return $phoneNumbers;
    }

    /**
     * Retrieves the ISO country code from the given OXID user or address object.
     *
     * @param object $object
     *  OXID User or Address object
     * @return string
     * - Country ISO code
     */
    private function getCountryIso(object $object): string
    {
        $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $country->load($object->oxaddress__oxcountryid->value ?? $object->oxuser__oxcountryid->value);
        return $country->oxcountry__oxisoalpha2->value;
    }

    /**
     * Maps OXID gender to Ginger Plugin SDK gender.
     *
     * @param string $oxidGender
     *  OXID gender value
     * @return string|null
     * - Mapped gender value or null if not applicable
     */
    private function mapGender(string $oxidGender): ?string
    {
        return match (strtolower($oxidGender)) {
            'mr' => 'male',
            'mrs' => 'female',
            default => null,
        };
    }

    /**
     * Formats address into a single string.
     *
     * @param object $userOrAddress
     *  The OXID user or address object.
     * @return string
     * - Formatted address string
     */
    private function formatAddress(object $userOrAddress): string
    {
        $street = $userOrAddress->oxuser__oxstreet->value ?? $userOrAddress->oxaddress__oxstreet->value;
        $streetNumber = $userOrAddress->oxuser__oxstreetnr->value ?? $userOrAddress->oxaddress__oxstreetnr->value;
        $zip = $userOrAddress->oxuser__oxzip->value ?? $userOrAddress->oxaddress__oxzip->value;
        $city = $userOrAddress->oxuser__oxcity->value ?? $userOrAddress->oxaddress__oxcity->value;

        return "$street $streetNumber $zip $city";
    }
}

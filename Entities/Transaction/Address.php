<?php

namespace GingerPlugins\Entities\Transaction;

/**
 *
 * @author  Adriaan Meijer
 * @version 1.0    - Initiele opzet
 *
 */
class Address
{
    /**
     * @var string
     */
    protected $first_name = '';

    /**
     * @var string
     */
    protected $last_name = '';

    /**
     * @var string
     */
    protected $email = '';

    /**
     * @var string
     */
    protected $phone_number = '';

    /**
     * @var string
     */
    protected $street = '';

    /**
     * @var string
     */
    protected $house_number = '';

    /**
     * @var string
     */
    protected $house_extension = '';

    /**
     * @var string
     */
    protected $postal_code = '';

    /**
     * @var string
     */
    protected $city = '';

    /**
     * @var string
     */
    protected $state = '';

    /**
     * @var string
     */
    protected $country = '';

    /**
     * @var string
     */
    protected $gender = '';

    public function __construct(\stdClass $oObject)
    {
        if (isset($oObject->first_name)) {
            $this->first_name = $oObject->first_name;
        }

        if (isset($oObject->last_name)) {
            $this->last_name = $oObject->last_name;
        }

        if (isset($oObject->email)) {
            $this->email = $oObject->email;
        }

        if (isset($oObject->phone_number)) {
            $this->phone_number = $oObject->phone_number;
        }

        if (isset($oObject->street)) {
            $this->street = $oObject->street;
        }

        if (isset($oObject->house_number)) {
            $this->house_number = $oObject->house_number;
        }
        if (isset($oObject->house_extension)) {
            $this->house_extension = $oObject->house_extension;
        }
        if (isset($oObject->postal_code)) {
            $this->postal_code = $oObject->postal_code;
        }
        if (isset($oObject->city)) {
            $this->city = $oObject->city;
        }
        if (isset($oObject->state)) {
            $this->state = $oObject->state;
        }
        if (isset($oObject->country)) {
            $this->country = $oObject->country;
        }
        if (isset($oObject->gender)) {
            $this->gender = $oObject->gender;
        }
    }

    /**
     * @return \stdClass
     */
    public function toStdClass()
    {
        $oObject = new \stdClass();
        $oObject->first_name = $this->first_name;
        $oObject->last_name = $this->last_name;
        $oObject->email = $this->email;
        $oObject->phone_number = $this->phone_number;
        $oObject->street = $this->street;
        $oObject->house_number = $this->house_number;
        $oObject->house_extension = $this->house_extension;
        $oObject->postal_code = $this->postal_code;
        $oObject->city = $this->city;
        $oObject->state = $this->state;
        $oObject->country = $this->country;
        $oObject->gender = $this->gender;
        return $oObject;
    }

    /**
     * @return string
     */
    public function GetFirstName()
    {
        return $this->first_name;
    }

    /**
     * @return string
     */
    public function GetLastName()
    {
        return $this->last_name;
    }

    /**
     * @return string
     */
    public function GetEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function GetPhoneNumber()
    {
        return $this->phone_number;
    }

    /**
     * @return string
     */
    public function GetStreet()
    {
        return $this->street;
    }

    /**
     * @return string
     */
    public function GetHouseNumber()
    {
        return $this->house_number;
    }

    /**
     * @return string
     */
    public function GetHouseExtension()
    {
        return $this->house_extension;
    }

    /**
     * @return string
     */
    public function GetPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * @return string
     */
    public function GetCity()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function GetState()
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function GetCountry()
    {
        return $this->country;
    }

    /**
     * @param string $first_name
     *
     * @return Address
     */
    public function SetFirstName($first_name)
    {
        if (empty($first_name) || is_string($first_name)) {
            return $this;
        }
        $this->first_name = $first_name;
        return $this;
    }

    /**
     * @param string $last_name
     *
     * @return Address
     */
    public function SetLastName($last_name)
    {
        if (empty($last_name) || is_string($last_name)) {
            return $this;
        }
        $this->last_name = $last_name;
        return $this;
    }

    /**
     * @param string $email
     *
     * @return Address
     */
    public function SetEmail($email)
    {
        if (empty($email) || is_string($email)) {
            return $this;
        }
        $this->email = $email;
        return $this;
    }

    /**
     * @param string $phone_number
     *
     * @return Address
     */
    public function SetPhoneNumber($phone_number)
    {
        if (empty($phone_number) || is_string($phone_number)) {
            return $this;
        }
        $this->phone_number = $phone_number;
        return $this;
    }

    /**
     * @param string $street
     *
     * @return Address
     */
    public function SetStreet($street)
    {
        if (empty($street) || is_string($street)) {
            return $this;
        }
        $this->street = $street;
        return $this;
    }

    /**
     * @param string $house_number
     *
     * @return Address
     */
    public function SetHouseNumber($house_number)
    {
        if (empty($house_number) || is_string($house_number)) {
            return $this;
        }
        $this->house_number = $house_number;
        return $this;
    }

    /**
     * @param string $house_extension
     *
     * @return Address
     */
    public function SetHouseExtension($house_extension)
    {
        if (empty($house_extension) || is_string($house_extension)) {
            return $this;
        }
        $this->house_extension = $house_extension;
        return $this;
    }

    /**
     * @param string $postal_code
     *
     * @return Address
     */
    public function SetPostalCode($postal_code)
    {
        if (empty($postal_code) || is_string($postal_code)) {
            return $this;
        }
        $this->postal_code = $postal_code;
        return $this;
    }

    /**
     * @param string $city
     *
     * @return Address
     */
    public function SetCity($city)
    {
        if (empty($city) || is_string($city)) {
            return $this;
        }
        $this->city = $city;
        return $this;
    }

    /**
     * @param string $state
     *
     * @return Address
     */
    public function SetState($state)
    {
        if (empty($state) || is_string($state)) {
            return $this;
        }
        $this->state = $state;
        return $this;
    }

    /**
     * @param string $country
     *
     * @return Address
     */
    public function SetCountry($country)
    {
        if (empty($country) || is_string($country)) {
            return $this;
        }
        $this->country = substr($country, 0, 2);
        return $this;
    }

    /**
     * @param string $gender
     *
     * @return Address
     */
    public function SetGender($gender)
    {
        if (empty($gender) || is_string($gender)) {
            return $this;
        }
        $this->gender = $gender;
        return $this;
    }
}

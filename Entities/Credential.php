<?php

namespace GingerPlugins\Entities;

class Credential
{
    /**
     * @var string Public key used to connect to the api.
     */
    private $ApiPublic;

    /**
     * @var string Secret key used to connect to the api
     */
    private $ApiSecret;

    /**
     * @var string URI to connect to the api
     */
    private $ApiRoot;

    /**
     * @var string Once the user has successfully installed the app, return him here.
     */
    private $ReturnUrl;

    /**
     * @var string Create date of this Credential. Used for example purposes only.
     */
    private $CreateDate;

    /**
     * @var string Ginger API key.
     */
    private $GingerApiKey;

    /**
     * @var string Ginger AfterPay Test API key.
     */
    private $GingerAfterpayTestApiKey;

    /**
     * @var string Ginger Klarna Test API key.
     */
    private $GingerKlarnaTestApiKey;

    /**
     * @var string Ginger Afterpay allowed countries.
     */
    private $GingerAfterpayCountries;

    /**
     * @var string Store Id.
     */
    private $StoreId;

    public function __construct(\stdClass $oObject)
    {
        $this->SetApiPublic($oObject->api_public);
        $this->SetApiSecret($oObject->api_secret);
        $this->SetApiRoot($oObject->api_root);
        if (isset($oObject->return_url)) {
            $this->SetReturnUrl($oObject->return_url);
        }
        if (isset($oObject->ginger_api_key)) {
            $this->SetGingerApiKey($oObject->ginger_api_key);
        }
        if (isset($oObject->ginger_afterpay_test_api_key)) {
            $this->SetGingerAfterpayTestApiKey($oObject->ginger_afterpay_test_api_key);
        }
        if (isset($oObject->ginger_klarna_test_api_key)) {
            $this->SetGingerKlarnaTestApiKey($oObject->ginger_klarna_test_api_key);
        }
        if (isset($oObject->ginger_afterpay_countries)) {
            $this->SetGingerAfterpayCountries($oObject->ginger_afterpay_countries);
        }
    }

    /**
     * Convert this credential object to an array
     * @return array
     */
    public function ToArray()
    {
        return [
              'api_public' => $this->ApiPublic,
              'api_secret' => $this->ApiSecret,
              'api_root' => $this->ApiRoot,
              'return_url' => $this->ReturnUrl,
              'ginger_api_key' => $this->GingerApiKey,
              'ginger_afterpay_api_key' => $this->GingerAfterpayTestApiKey,
              'ginger_klarna_api_key' => $this->GingerKlarnaTestApiKey,
              'ginger_afterpay_countries' => $this->GingerAfterpayCountries,
              'store_id' => $this->StoreId
        ];
    }

    /**
     * Convert this credential object to an std object
     * @return object
     */
    public function ToStd()
    {
        return (object)$this->ToArray();
    }

    /**
     * Print this credential as an array
     * @return string
     */
    public function __toString()
    {
        return print_r($this->ToArray(), 1);
    }

    /**
     * @return string
     */
    public function GetApiPublic()
    {
        return $this->ApiPublic;
    }

    /**
     * @param string $ApiPublic
     */
    public function SetApiPublic($ApiPublic)
    {
        $this->ApiPublic = $ApiPublic;
    }

    /**
     * @return string
     */
    public function GetApiRoot()
    {
        return $this->ApiRoot;
    }

    /**
     * @param string $ApiRoot
     */
    public function SetApiRoot($ApiRoot)
    {
        $this->ApiRoot = $ApiRoot;
    }

    /**
     * @return string
     */
    public function GetApiSecret()
    {
        return $this->ApiSecret;
    }

    /**
     * @param string $ApiSecret
     */
    public function SetApiSecret($ApiSecret)
    {
        $this->ApiSecret = $ApiSecret;
    }

    /**
     * @param string $CreateDate
     */
    public function SetCreateDate($CreateDate)
    {
        $this->CreateDate = $CreateDate;
    }

    /**
     * @return string
     */
    public function GetReturnUrl()
    {
        return $this->ReturnUrl;
    }

    /**
     * @param string $ReturnUrl
     */
    public function SetReturnUrl($ReturnUrl)
    {
        $this->ReturnUrl = $ReturnUrl;
    }

    /**
     * @return string
     */
    public function GetGingerApiKey()
    {
        return $this->GingerApiKey;
    }

    /**
     * @param string $GingerApiKey
     */
    public function SetGingerApiKey($GingerApiKey)
    {
        $this->GingerApiKey = $GingerApiKey;
    }

    /**
     * @return string
     */
    public function GetGingerAfterpayTestApiKey()
    {
        return $this->GingerAfterpayTestApiKey;
    }

    /**
     * @param string $GingerAfterpayTestApiKey
     *
     */
    public function SetGingerAfterpayTestApiKey($GingerAfterpayTestApiKey)
    {
        $this->GingerAfterpayTestApiKey = $GingerAfterpayTestApiKey;
    }

    /**
     * @param string $GingerKlarnaTestApiKey
     */
    public function GetGingerKlarnaTestApiKey()
    {
        return $this->GingerKlarnaTestApiKey;
    }

    /**
     * @return string
     */
    public function SetGingerKlarnaTestApiKey($GingerKlarnaTestApiKey)
    {
        $this->GingerKlarnaTestApiKey = $GingerKlarnaTestApiKey;
    }

    /**
     * @return string
     */
    public function GetGingerAfterpayCountries()
    {
        return $this->GingerAfterpayCountries;
    }

    /**
     * @param string $GingerAfterpayCountries
     */
    public function SetGingerAfterpayCountries($GingerAfterpayCountries)
    {
        $this->GingerAfterpayCountries = $GingerAfterpayCountries;
    }

    /**
     * @return string
     */
    public function GetStoreId()
    {
        return $this->StoreId;
    }

    /**
     * @param string $StoreId
     */
    public function SetStoreId($StoreId)
    {
        $this->StoreId = $StoreId;
    }
}
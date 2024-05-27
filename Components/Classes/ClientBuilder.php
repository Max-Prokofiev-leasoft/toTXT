<?php

namespace GingerPlugins\Components\Classes;

use Ginger\ApiClient;
use Ginger\Ginger;
use GingerPlugins\Classes\CodeBlockTrait;
use GingerPlugins\Components\Configurators\BankConfig;
use GingerPlugins\Data\Data_Credential;
use GingerPlugins\Entities\Credential;

class ClientBuilder
{
    use CodeBlockTrait;
    public ApiClient $client;
    public $credentials;

    /**
     * @throws \GingerPlugins\Exceptions\InvalidApiResponse
     */
    public function __construct($method, $credential)
    {
        $this->credentials = $credential;

        if (!$this->credentials) {
            return false;
        }

        switch ($method) {
            case 'afterpay':
                $apiKey = !empty(
                $this->credentials->GetGingerAfterpayTestApiKey()
                ) ? $this->credentials->GetGingerAfterpayTestApiKey() : $this->credentials->GetGingerApiKey();
                break;
            case 'klarna-pay-later':
                $apiKey = !empty(
                $this->credentials->GetGingerKlarnaTestApiKey()
                ) ? $this->credentials->GetGingerKlarnaTestApiKey() : $this->credentials->GetGingerApiKey();
                break;
            default:
                $apiKey = $this->credentials->GetGingerApiKey();
        }

        if ($apiKey) {
            try {
                $this->client = Ginger::createClient(BankConfig::BANK_API_ENDPOINT, $apiKey, []);
            } catch (\Exception $oEx) {
                Helper::logGingerError(__FILE__, 'Emspay_Gateway', $oEx->getMessage());
                $this->addCodeblocks($oEx->getMessage());

                header('HTTP/1.1 500 Internal Server Error', true, 500);
                echo $oEx->getMessage();
                die();
            }
        }
    }

    /**
     * Get issuers for iDeal payment method
     *
     * @return array
     */
    public function getIssuers(): array
    {
        $issuers = [];

        try {
            if (!isset($this->client)) {
                $this->__construct(null, $this->GetCredential());
            }
            $issuers = $this->client->getIdealIssuers();
        } catch (\Exception $e) {
            Helper::logGingerError(__FILE__, 'getIssuers', $e->getMessage());
        }
        return $issuers;
    }

}
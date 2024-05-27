<?php

namespace GingerPlugins\Services\TransactionRepository;
require_once __DIR__ . '/../../autoloader.php';

use GingerPlugin\Components\Classes\Redefiner;
use GingerPlugins\Classes\CodeBlockTrait;
use GingerPlugins\Components\Configurators\BankConfig;
use GingerPlugins\Components\Traits\AppConnectorTrait;
use GingerPlugins\Components\Traits\WebRequestTrait;
use GingerPlugins\Data\Data_Transaction;
use GingerPlugins\Data\Data_Credential;
use GingerPlugins\Entities\Transaction;
use GingerPlugins\Exceptions\InvalidHashException;
use GingerPlugins\Http\Hash;
use GingerPlugins\Json\JsonSerializer;
use GingerPlugins\Log\Log;
use JetBrains\PhpStorm\Pure;

class TransactionCollection
{
    use AppConnectorTrait;
    use WebRequestTrait;
    use CodeBlockTrait;

    private object $oCredential;

    public function post()
    {
        try {
            $transaction = $this->create();

            echo $transaction;
            die();
        } catch (\Exception $oEx) {
            Log::Write('Endpoint', 'ERROR', 'HTTP/1.1 500 Internal Server Error. ' . $oEx->getMessage());
            Log::WriteEndCall(__FILE__);
            header('HTTP/1.1 500 Internal Server Error', true, 500);

            $output = new \stdClass();
            $output->status = 'FAILED';
            $output->error = $oEx->getMessage();
            $response = JsonSerializer::Serialize($output);
            $hash = (new Hash())->AddData(BankConfig::AppUri . $_SERVER['REQUEST_URI'])->AddData($response)->Hash();

            header(BankConfig::HeaderXHash . ': ' . $hash);
            echo $response;
            die();
        }
    }


    /**
     * @param string $sTransactionId
     * @return string
     * @throws \GingerPlugins\Exceptions\InvalidApiResponse
     * @throws \GingerPlugins\Exceptions\InvalidCredentialException
     * @throws \GingerPlugins\Exceptions\InvalidHashException
     * @throws \GingerPlugins\Exceptions\InvalidJsonException
     */
    public function getStatus(string $sTransactionId = ''): string
    {
        $this->VerifyHash();

        $sTransaction = $this->createTransactionObject($sTransactionId);
        $oTransaction = new Transaction($sTransaction);

        $sResponse = JsonSerializer::Serialize($oTransaction->toStdClass());
        $oHash = new Hash($this->oCredential->GetApiSecret());
        $sHash = $oHash->AddData(BankConfig::AppUri . $_SERVER['REQUEST_URI'])->AddData($sResponse)->Hash();

        header('HTTP/1.1 200 OK', true, 200);
        header(BankConfig::HeaderXHash . ': ' . $sHash);

        return $sResponse;
    }

    /**
     *
     * @return string
     * @throws InvalidHashException
     * @throws \Exception
     */
    public function create(): string
    {
        $sIncomingData = @file_get_contents('php://input');

        Log::Write('TransactionCollection', 'INPUT_BODY', $sIncomingData);
        $this->VerifyHash($sIncomingData);

        $oPostedData = JsonSerializer::DeSerialize($sIncomingData);
        $oTransaction = new Transaction($oPostedData);

        $redefiner = new Redefiner(
            $oTransaction->GetMethod(),
            $this->oCredential
        );

        $resultTransaction = $redefiner->createOrder($oPostedData);

        if (array_key_exists('transactions', $resultTransaction) &&
            array_key_exists('payment_url', current($resultTransaction['transactions']))) {
            $pay_url = current($resultTransaction['transactions'])['payment_url'];
        } elseif (array_key_exists('order_url', $resultTransaction)) {
            $pay_url = $resultTransaction['order_url'];
        } else {
            $pay_url = null;
        }

        if ($pay_url) {
            $oTransaction->SetPayUrl($pay_url);
        } else {
            $oTransaction->SetPayUrl($resultTransaction['return_url']);
        }

        Data_Transaction::insert($oTransaction->GetTransactionId(), $resultTransaction['id'], 'OPEN');

        $sResponse = JsonSerializer::Serialize($oTransaction->toStdClass());

        Log::Write('TransactionCollection', 'OUTPUT_BODY', $sResponse);

        $oHash = new Hash($this->oCredential->GetApiSecret());
        $sHash = $oHash
            ->AddData(BankConfig::AppUri . $_SERVER['REQUEST_URI'])
            ->AddData($sResponse)
            ->Hash();

        header('Content-Type' . ':' . 'application/x-www-form-urlencoded');
        header(BankConfig::HeaderXHash . ': ' . $sHash, true);
        header('HTTP/1.1 200 OK', true, 200);
        header('Accept: */*');
        return $sResponse;
    }

    /**
     * @param string $sIncomingData
     *
     * @throws InvalidHashException
     * @throws \GingerPlugins\Exceptions\InvalidCredentialException
     */
    protected function VerifyHash(string $sIncomingData = '')
    {
        $aRequestHeaders = apache_request_headers();

        $sApiPublic = $aRequestHeaders[Hash::Header_Public];

        $this->oCredential = Data_Credential::GetOneByApiPublic($sApiPublic);

        if (!$this->oCredential) {
            throw new InvalidHashException();
        }
        #Validate if the data we received is correct and authenticated.
        $oIncomingHash = new Hash($this->oCredential->GetApiSecret());
        $oIncomingHash->AddData(BankConfig::AppUri . $_SERVER['REQUEST_URI']);

        if (!empty($sIncomingData)) {
            $oIncomingHash->AddData($sIncomingData);
        }

        $bValid = $oIncomingHash->IsValid($aRequestHeaders[Hash::Header_Hash]);

        if ($bValid === false) {
            throw new InvalidHashException();
        }
    }

    /**
     * @param string $sTransactionId
     * @return \stdClass
     * @throws \GingerPlugins\Exceptions\InvalidApiResponse
     * @throws \GingerPlugins\Exceptions\InvalidCredentialException
     * @throws \GingerPlugins\Exceptions\InvalidJsonException
     */
    public function createTransactionObject(string $sTransactionId): \stdClass
    {
        $gingerOrderId = Data_Transaction::GetRowByField('ginger_order_id', 'transaction_id', $sTransactionId);
        $ccvTransactionStatus = 'OPEN';
        if ($gingerOrderId) {
            $redefiner = new Redefiner('', $this->oCredential);
            $ginger_order = $redefiner->client->getOrder($gingerOrderId);
            if (empty($ginger_order)) {
                throw new \Exception('Order ' . $gingerOrderId . ' does not exist in the Ginger system!');
            }

            if ($ginger_order['status'] == 'error') {
                $this->addCodeblocks(current($ginger_order['transactions'])['customer_message']);
            }

            $ccvTransactionStatus = Redefiner::TransformGingerOrderToCCVTransactionStatus($ginger_order);

        }
        $sOutput = $this->makeWebRequest(
            'orders/?ordernumber=' . $ginger_order['merchant_order_id'],
            'GET',
            '',
            $this->oCredential
        );
        $orderInfo = JsonSerializer::DeSerialize($sOutput);
        $sTransaction = new \stdClass();
        $sTransaction->amount = $ginger_order['amount'];
        $sTransaction->currency = $ginger_order['currency'];
        $sTransaction->status = (string)$ccvTransactionStatus;
        $sTransaction->order_id = (int)$orderInfo->items[0]->id;
        $sTransaction->order_number = (int)$ginger_order['merchant_order_id'];
        $sTransaction->language = (string)$orderInfo->items[0]->orderedinlng;
        $sTransaction->method = (string)$ginger_order['transactions'][0]['payment_method'];
        $sTransaction->issuer = $ginger_order['transactions'][0]['payment_method_details']['issuer_id'] ?? null;
        $sTransaction->return_url = $ginger_order['return_url'];
        $sTransaction->webhook_url = $ginger_order['extra']['webhook_url'];
        $sTransaction->pay_url = isset($ginger_order['transactions'][0]['payment_url']) ? $ginger_order['return_url'] : null;
        $sTransaction->transaction_id = $sTransactionId;
        $sTransaction->billing_address = $this->getAddress($orderInfo->items[0]->customer->billingaddress);
        $sTransaction->shipping_address = $this->getAddress($orderInfo->items[0]->customer->deliveryaddress);
        $sTransaction->date_of_birth = $orderInfo->items[0]->customer->birthdate;
        $sTransaction->created = $orderInfo->items[0]->create_date;
        $sTransaction->ipv4_address = $ginger_order['extra']['ipv4_address'] ?? '';
        $sTransaction->error = null;

        return $sTransaction;
    }

    /**
     * @param object $oAddress
     *
     * @return object
     */
    #[Pure] public function getAddress(object $oAddress): object
    {
        $Address = new \stdClass();
        $Address->first_name = $oAddress->first_name ?? '';
        $Address->last_name = $oAddress->last_name ?? '';
        $Address->email = $oAddress->email ?? '';
        $Address->phone_number = $oAddress->telephone;
        $Address->street = $oAddress->street;
        $Address->house_number = isset($oAddress->housenumber) ? (string)$oAddress->housenumber : null;
        $Address->house_extension = $oAddress->housenumber_suffix;
        $Address->postal_code = $oAddress->zipcode;
        $Address->city = $oAddress->city;
        $Address->country = $oAddress->country_code;
        $Address->gender = $oAddress->gender;

        return $Address;
    }
}
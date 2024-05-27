<?php

namespace GingerPlugins\Services\Scripts;

use GingerPlugin\Components\Classes\Redefiner;
use GingerPlugins\Components\Classes\FunctionalityGateway;
use GingerPlugins\Components\Classes\Helper;
use GingerPlugins\Components\Traits\WebRequestTrait;
use GingerPlugins\Exceptions\InvalidJsonException;
use GingerPlugins\Data\Data_Credential;
use GingerPlugins\Json\JsonSerializer;
use GingerPlugins\Log\Log;

require_once __DIR__ . '/../../autoloader.php';

class CCVShopStatusChangeWebhook
{
    use WebRequestTrait;

    private $connection;
    private $credentials;
    private $storeId;

    public function __construct()
    {
        try {
            // Get contents of webhook request
            Log::WriteStartCall(__FILE__);
            $requestBody = json_decode(file_get_contents('php://input'), true);
            Log::Write(__FUNCTION__, 'INPUT', JsonSerializer::Serialize($requestBody));
            if (!is_array($requestBody)) {
                throw new InvalidJsonException();
            }

            if (!empty($requestBody)) {
                Log::Write(__FUNCTION__, 'CHECK STATUS', 'Required : 6, retrieved : '.$requestBody['status']);
                if ($requestBody['status'] == 5) {
                    $this->storeId = filter_input(INPUT_GET, 'storeId', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                    $this->credentials = Data_Credential::GetOneByStoreId($this->storeId);
                    $this->ship_an_order($requestBody);
                }
            } else {
                throw new \Exception('Unauthorised action!');
            }
        } catch (\Exception $oEx) {
            Helper::logGingerError(__FILE__, 'construct', $this->storeId . ' ' . $oEx->getMessage());
            die($oEx->getMessage());
        }
    }

    /**
     * Support for Klarna Pay Later and Afterpay order shipped state
     *
     * @param array $requestBody
     */
    function ship_an_order(array $requestBody)
    {
        Log::Write(__FUNCTION__, 'START');
        try {
            http_response_code(200);
            $redefiner = new Redefiner('', $this->credentials);

            $sOutput = $this->makeWebRequest(
                'orders/?ordernumber=' . $requestBody['order_number'],
                'GET',
                ''
            );
            Log::Write(__FUNCTION__, 'CCVSHOP ORDER', $sOutput);
            $orderData = JsonSerializer::DeSerialize($sOutput);

            $sOutput = $this->makeWebRequest(
                'orders/' . $orderData->items[0]->id . '/ordernotes/',
                'GET',
                ''
            );
            Log::Write(__FUNCTION__, 'CCVSHOP ORDER NOTE', $sOutput);
            $orderNotes = JsonSerializer::DeSerialize($sOutput);

            if (!empty($orderNotes->items)) {
                $emsOrderId = $orderNotes->items[0]->note;
                if (empty($ginger_order = $redefiner->client->getOrder($emsOrderId))) {
                    throw new \Exception('Order ' . $emsOrderId . ' does not exist in the Ginger system!');
                }
                Log::Write(__FUNCTION__, 'CHECK BEFORE CAPTURE', $emsOrderId);
                if (in_array($requestBody['previous_status'], [1, 2, 3, 4, 5, 7]) &&
                    current($ginger_order['transactions'])['is_capturable']) {
                    Log::Write(__FUNCTION__, 'CAPTURING');
                    $transaction_id = !empty(current($ginger_order['transactions'])) ? current(
                        $ginger_order['transactions']
                    )['id'] : null;
                    $redefiner->client->captureOrderTransaction($ginger_order['id'], $transaction_id);
                    Log::Write(__FUNCTION__, 'CAPTURED SUCCESSFULLY');
                }
            }
            Log::WriteEndCall(__FILE__);
        } catch (\Exception $oEx) {
            Helper::logGingerError(__FILE__, 'Webhook_CCV', $this->storeId . ' ' . $oEx->getMessage());
            die($oEx->getMessage());
        }
    }
}

new CCVShopStatusChangeWebhook();
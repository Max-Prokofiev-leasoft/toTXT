<?php

namespace GingerPlugins\Services\Scripts;

require_once __DIR__ . '/../../autoloader.php';

use Ginger\ApiClient;
use GingerPlugin\Components\Classes\Redefiner;
use GingerPlugins\Components\Classes\Helper;
use GingerPlugins\Components\Configurators\BankConfig;
use GingerPlugins\Data\Data_Transaction;
use GingerPlugins\Json\JsonSerializer;
use GingerPlugins\Log\Log;
use GingerPlugins\Services\TransactionRepository\TransactionCollection;


class Webhook
{
    private ApiClient $client;
    private array $data;

    public function __construct()
    {
        try {
            Log::Write(__FUNCTION__);
            $ccvshop_order_number = filter_input(INPUT_GET, 'order_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $store_id = filter_input(INPUT_GET, 'storeId', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = json_decode(file_get_contents("php://input"), true);
            if (!is_array($data)) {
                Helper::logGingerError(__FILE__, 'prepare', 'Invalid JSON!');
                throw new \Exception('Invalid JSON!');
            }
            Log::Write(__FUNCTION__, 'GET_DATA', JsonSerializer::Serialize($data));

            if (!$store_id or !$ccvshop_order_number) {
                Helper::logGingerError(__FILE__, 'prepare', 'Access forbidden!');
                throw new \Exception('Access forbidden!');
            }

            if (!empty($data['event']) and $data['event'] == 'status_changed') {
                $this->data = $data;
            } else {
                Helper::logGingerError(__FILE__, 'prepare', 'Unauthorised action!');
                throw new \Exception('Unauthorised action!');
            }
        } catch (\Exception $oEx) {
            Helper::logGingerError(__FILE__, 'prepare', $oEx->getMessage());
            die($oEx->getMessage());
        }
    }

    /**
     * Handling POST request from GINGER API after order status is changed
     *
     */
    public function process(): bool
    {
        $this->client = (new Redefiner('', Helper::getCredentials()))->client;

        if (!$this->data) {
            return false;
        }

        try {
            $gingerOrder = $this->client->getOrder($this->data['order_id']);

            Log::Write(__FUNCTION__, 'GET_ORDER', JsonSerializer::Serialize($gingerOrder));

            if (!empty($gingerOrder)) {
                $currentTransactionStatus = Data_Transaction::GetRowByField(
                    'status',
                    'ginger_order_id',
                    $this->data['order_id']
                );

                $ccvTransactionStatus = BankConfig::GINGER_TO_CCVSHOP_ORDER_STATUS[$gingerOrder['status']];

                Log::Write(__FUNCTION__, 'START UPDATING CCVSHOP ORDER STATUS', 'CCVSHOP: ' . $ccvTransactionStatus . '. GINGER: ' . $gingerOrder['status']);

                if ($currentTransactionStatus != $ccvTransactionStatus) {
                    $webhook_url = $gingerOrder['extra']['webhook_url'];

                    $rCurlHandler = curl_init();
                    curl_setopt($rCurlHandler, CURLOPT_URL, $webhook_url);
                    curl_setopt($rCurlHandler, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYHOST, 0);
                    $result = curl_exec($rCurlHandler);
                    curl_close($rCurlHandler);

                    Log::Write(__FUNCTION__, 'RESULT OF UPDATING ORDER STATUS', JsonSerializer::Serialize($result));
                    $oData = [
                        'status' => $ccvTransactionStatus,
                        'date' => date('Y-m-d h:i:s')
                    ];
                    Data_Transaction::Update($oData, 'ginger_order_id', $this->data['order_id']);
                }

                header('HTTP/1.1 200 OK', true, 200);
                header('Content-type: application/json');
                echo JsonSerializer::Serialize($gingerOrder);
                Log::WriteEndCall(__FILE__);
                die();
            }
        } catch (\Exception $oEx) {
            Helper::logGingerError(__FILE__, __FUNCTION__, $oEx->getMessage());
            die($oEx->getMessage());
        }
    }
}

Log::WriteStartCall(__FILE__);
$data = json_decode(file_get_contents("php://input"), true);
Log::Write('__CONSTRUCT::BEFORE', 'RETRIEVED DATA', JsonSerializer::Serialize($data));
if (is_array($data) && array_key_exists('event', $data) && $data['event'] == 'status_changed') {
    $webhook = new Webhook();
    $webhook->process();
}

<?php

namespace GingerPlugins\Components\Classes;

use GingerPlugin\Components\Classes\Redefiner;
use GingerPlugins\Components\Classes\FunctionalityGateway;
use GingerPlugins\Components\Traits\WebRequestTrait;
use GingerPlugins\Data\Data_Credential;
use GingerPlugins\Entities\Credential;
use GingerPlugins\Exceptions\InvalidApiResponse;
use GingerPlugins\Http\Hash;
use GingerPlugins\Json\JsonSerializer;
use GingerPlugins\Log\Log;

class Helper extends ClientBuilder
{
    use WebRequestTrait;

    /**
     * @return Credential
     * @throws \Exception
     */
    public static function getCredentialFromData(): Credential
    {
        $data = json_decode(@file_get_contents('php://input'), true);

        $credentials = new \stdClass();
        $credentials->api_public = $data['api_public'];
        $credentials->api_secret = $data['api_secret'];
        $credentials->api_root = $data['api_root'];
        $credentials->return_url = $data['return_url'];

        return new Credential($credentials);
    }

    public static function getCredentials()
    {
        try {
            $aRequestHeaders = apache_request_headers();
            if (isset($aRequestHeaders[Hash::Header_Public])) {
                $apiPublic = $aRequestHeaders[Hash::Header_Public];
                $credentials = Data_Credential::GetOneByApiPublic($apiPublic);
            } elseif (isset($_POST['api_public'])) {
                $apiPublic = filter_input(INPUT_POST, 'api_public', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $credentials = Data_Credential::GetOneByApiPublic($apiPublic);
            } elseif (isset($_GET['api_public'])) {
                $api_public = filter_input(INPUT_GET, 'api_public', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $credentials = Data_Credential::GetOneByApiPublic($api_public);
            } elseif (isset($_GET['storeId'])) {
                $store_id = filter_input(INPUT_GET, 'storeId', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $credentials = Data_Credential::GetOneByStoreId($store_id);
            } else {
                $credentials = Helper::getCredentialFromData();
            }
            return $credentials;
        } catch (\Exception $exception) {
            print_r($exception->getMessage());
            exit;
        }
    }

    /**
     * @return string
     * @throws InvalidApiResponse | \Exception
     */
    public function getStoreId(): string
    {
        $sOutput = $this->makeWebRequest('webshops/', 'GET', '', $this->credentials);

        if (!$sOutput) {
            return '';
        }

        $aCollectionOfApps = JsonSerializer::DeSerialize($sOutput);

        if (!isset($aCollectionOfApps->items)) {
            throw new InvalidApiResponse('Collection contained zero apps. Expected 1.');
        }

        if (count($aCollectionOfApps->items) > 1) {
            throw new InvalidApiResponse(
                'Collection contained ' . count($aCollectionOfApps->items) . ' apps. Expected 1.'
            );
        }

        return $aCollectionOfApps->items[0]->id;
    }

    /**
     * Function log Ginger errors
     *
     * @param string $message
     */
    public static function logGingerError($file, $function, $message)
    {
        Log::WriteStartCall($file, true);
        Log::write($function, 'ERROR', $message, true);
        Log::writeEndCall($file, true);
    }

    /**
     * Transform Ginger order status to CCV Shop transaction status
     * @param $gingerOrder
     * @return string
     */
    public static function TransformGingerOrderToCCVTransactionStatus($gingerOrder): string
    {
        switch ($gingerOrder['status']) {
            case 'cancelled':
                Log::Write(__FUNCTION__, 'REASON', current($gingerOrder['transactions'])['reason'], true);
                $ccvTransactionStatus = 'CANCELLED';
                break;
            case 'expired':
                $ccvTransactionStatus = 'EXPIRED';
                break;
            case 'error':
                Log::Write(__FUNCTION__, 'REASON', current($gingerOrder['transactions'])['customer_message'], true);
                $ccvTransactionStatus = 'FAILED';
                break;
            case 'completed':
                $ccvTransactionStatus = 'SUCCESS';
                break;
            default:
                $ccvTransactionStatus = 'OPEN';
                break;
        }
        return $ccvTransactionStatus;
    }
}

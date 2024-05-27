<?php

namespace GingerPlugins\Components\Traits;

use GingerPlugins\Components\Classes\Helper;
use GingerPlugins\Data\Data_Credential;
use GingerPlugins\Entities\Credential;
use GingerPlugins\Exceptions\InvalidApiResponse;
use GingerPlugins\Http\Hash;
use GingerPlugins\Http\WebRequest;
use GingerPlugins\Json\JsonSerializer;

trait WebRequestTrait
{
    private string $apiBaseUrl = '/api/rest/v1/';

    /**
     * @param string $resource
     * @param string $method
     * @param string $data
     * @param string | null $credentials
     * @return string
     * @throws \GingerPlugins\Exceptions\InvalidApiResponse
     * @throws \Exception
     */
    public function makeWebRequest(string $resource, string $method, $data = '', $credentials = null): string
    {
        if (!$credentials) {
            $credentials = Helper::getCredentials();
        }

        $requestData = '';

        if (isset($credentials)) {
            $WebRequest = new WebRequest();
            $WebRequest->SetPublicKey($credentials->GetApiPublic());
            $WebRequest->SetSecretKey($credentials->GetApiSecret());
            $WebRequest->SetApiRoot($credentials->GetApiRoot());
            $WebRequest->SetApiResource($this->apiBaseUrl . $resource);

            if ($method !== 'GET' && !empty($data)) {
                $WebRequest->SetData($data);
            }

            switch ($method) {
                case 'DELETE':
                    $requestData = $WebRequest->Delete();
                    break;
                case 'GET':
                    $requestData = $WebRequest->Get();
                    break;
                case 'POST':
                    $requestData = $WebRequest->Post();
                    break;
                case 'PUT':
                    $requestData = $WebRequest->Put();
                    break;
                case 'PATCH':
                    $requestData = $WebRequest->Patch();
                    break;
            }
        }
        return $requestData;
    }

    /**
     * @throws \GingerPlugins\Exceptions\InvalidApiResponse
     * @throws \Exception
     */
    public function getAppId()
    {
        $response = $this->makeWebRequest('apps', 'GET');
        $aCollectionOfApps = JsonSerializer::DeSerialize($response);

        if (!isset($aCollectionOfApps->items)) {
            throw new InvalidApiResponse('Collection contained zero apps. Expected 1.');
        }

        if (count($aCollectionOfApps->items) > 1) {
            throw new InvalidApiResponse('Collection contained ' . count($aCollectionOfApps->items) . ' apps. Expected 1.');
        }

        return $aCollectionOfApps->items[0]->id;
    }

}
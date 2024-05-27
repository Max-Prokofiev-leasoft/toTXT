<?php

namespace GingerPlugins\Http;

use GingerPlugins\Exceptions\InvalidApiResponse;
use GingerPlugins\Json\JsonSerializer;
use GingerPlugins\Log\Log;

class WebRequest
{
    /**
     * @var string The "Public key" or "Api key" can be retrieved in the webshop, This should be the same as the header 'x-public'.
     */
    private $PublicKey = '';

    /**
     * @var string The "Secret key" or "Api secret" can be retrieved in the webshop.
     */
    private $SecretKey = '';

    /**
     * @var string The data that is being posted to the resource (only with POST or PATCH methods)
     */
    private $Data = '';

    /**
     * @var string The request URI minus the domain name
     */
    private $ApiRoot = '';

    /**
     * @var string The request domain without trailing slash
     */
    private $ApiResource = '';

    /**
     * @var string The accept language of this call.
     */
    private $acceptLanguage = null;

    /**
     * Makes a GET request to the REST API
     *
     * @return string
     * @throws InvalidApiResponse
     */
    public function Get()
    {
        #HTTP method in uppercase (ie: GET, POST, PATCH, DELETE)
        $sMethod = 'GET';
        $sTimeStamp = gmdate('c');

        #Creating the hash
        $sHashString = implode(
              '|',
              array(
                    $this->GetPublicKey(),
                    $sMethod,
                    $this->GetApiResource(),
                    '',
                    $sTimeStamp,
              )
        );

        $sHash = hash_hmac('sha512', $sHashString, $this->GetSecretKey());

        $rCurlHandler = curl_init();

        curl_setopt($rCurlHandler, CURLOPT_URL, $this->GetApiRoot() . $this->GetApiResource());
        curl_setopt($rCurlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($rCurlHandler, CURLOPT_CUSTOMREQUEST, $sMethod);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt(
              $rCurlHandler,
              CURLOPT_HTTPHEADER,
              array(
                    "x-date: " . $sTimeStamp,
                    "x-hash: " . $sHash,
                    "x-public: " . $this->GetPublicKey(),
                    "Content-Type: text/json",
              )
        );

        $sOutput = curl_exec($rCurlHandler);

        $iHTTPCode = curl_getinfo($rCurlHandler, CURLINFO_HTTP_CODE);
        curl_close($rCurlHandler);

        if ($iHTTPCode !== 200) {
            Log::Write('WebRequest', 'HttpCode was ' . $iHTTPCode . '. Expected 200. Response: ' . $sOutput);

            throw new InvalidApiResponse('HttpCode was ' . $iHTTPCode . '. Expected 200');
        }

        return $sOutput;
    }

    /**
     * Makes a DELETE request to the REST API
     *
     * @return string
     * @throws InvalidApiResponse
     */
    public function Delete()
    {
        #HTTP method in uppercase (ie: GET, POST, PATCH, DELETE)
        $sMethod = 'DELETE';
        $sTimeStamp = gmdate('c');

        #Creating the hash
        $sHashString = implode(
              '|',
              array(
                    $this->GetPublicKey(),
                    $sMethod,
                    $this->GetApiResource(),
                    $this->GetData(),
                    $sTimeStamp,
              )
        );

        $sHash = hash_hmac('sha512', $sHashString, $this->GetSecretKey());

        $rCurlHandler = curl_init();
        curl_setopt($rCurlHandler, CURLOPT_URL, $this->GetApiRoot() . $this->GetApiResource());
        curl_setopt($rCurlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($rCurlHandler, CURLOPT_CUSTOMREQUEST, $sMethod);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt(
              $rCurlHandler,
              CURLOPT_HTTPHEADER,
              array(
                    "x-date: " . $sTimeStamp,
                    "x-hash: " . $sHash,
                    "x-public: " . $this->GetPublicKey(),
                    "Content-Type: text/json",
              )
        );
        $sOutput = curl_exec($rCurlHandler);
        $iHTTPCode = curl_getinfo($rCurlHandler, CURLINFO_HTTP_CODE);
        curl_close($rCurlHandler);

        if ($iHTTPCode !== 204) {
            Log::Write(
                  'WebRequest',
                  'DELETE::ERROR',
                  'HttpCode was ' . $iHTTPCode . '. Expected 204. Response: ' . $sOutput
            );
            throw new InvalidApiResponse('HttpCode was ' . $iHTTPCode . '. Expected 204');
        }
        return $sOutput;
    }

    /**
     * Makes a POST request to the REST API
     *
     * @return string
     * @throws InvalidApiResponse
     */
    public function Post()
    {
        #HTTP method in uppercase (ie: GET, POST, PATCH, DELETE)
        $sMethod = 'POST';
        $sTimeStamp = gmdate('c');

        #Creating the hash
        $sHashString = implode(
              '|',
              array(
                    $this->GetPublicKey(),
                    $sMethod,
                    $this->GetApiResource(),
                    $this->GetData(),
                    $sTimeStamp,
              )
        );

        $sHash = hash_hmac('sha512', $sHashString, $this->GetSecretKey());

        $rCurlHandler = curl_init();
        curl_setopt($rCurlHandler, CURLOPT_URL, $this->GetApiRoot() . $this->GetApiResource());
        curl_setopt($rCurlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($rCurlHandler, CURLOPT_POSTFIELDS, $this->GetData());
        curl_setopt($rCurlHandler, CURLOPT_CUSTOMREQUEST, $sMethod);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt(
              $rCurlHandler,
              CURLOPT_HTTPHEADER,
              array(
                    "x-date: " . $sTimeStamp,
                    "x-hash: " . $sHash,
                    "x-public: " . $this->GetPublicKey(),
                    "Content-Type: text/json",
              )
        );
        $sOutput = curl_exec($rCurlHandler);

        $iHTTPCode = curl_getinfo($rCurlHandler, CURLINFO_HTTP_CODE);
        curl_close($rCurlHandler);

        $this->SetData('');

        if (!in_array($iHTTPCode, array(200, 201))) {
            Log::Write(
                  'WebRequest',
                  'POST::ERROR',
                  'HttpCode was ' . $iHTTPCode . '. Expected 200|201 on [POST] ' . $this->GetApiRoot(
                  ) . $this->GetApiResource() . '. Response: ' . $sOutput
            );
            throw new InvalidApiResponse(
                  'HttpCode was ' . $iHTTPCode . '. Expected 200|201 on [POST] ' . $this->GetApiRoot(
                  ) . $this->GetApiResource()
            );
        }
        return $sOutput;
    }

    /**
     * Makes a PATCH request to the REST API
     *
     * @return string
     * @throws InvalidApiResponse
     */
    public function Patch()
    {
        #HTTP method in uppercase (ie: GET, POST, PATCH, DELETE)
        $sMethod = 'PATCH';
        $sTimeStamp = gmdate('c');

        #Creating the hash
        $sHashString = implode(
              '|',
              array(
                    $this->GetPublicKey(),
                    $sMethod,
                    $this->GetApiResource(),
                    $this->GetData(),
                    $sTimeStamp,
              )
        );

        $sHash = hash_hmac('sha512', $sHashString, $this->GetSecretKey());

        $rCurlHandler = curl_init();
        curl_setopt($rCurlHandler, CURLOPT_URL, $this->GetApiRoot() . $this->GetApiResource());
        curl_setopt($rCurlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($rCurlHandler, CURLOPT_POSTFIELDS, $this->GetData());
        curl_setopt($rCurlHandler, CURLOPT_CUSTOMREQUEST, $sMethod);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt(
              $rCurlHandler,
              CURLOPT_HTTPHEADER,
              array(
                    "x-date: " . $sTimeStamp,
                    "x-hash: " . $sHash,
                    "x-public: " . $this->GetPublicKey(),
                    "Content-Type: text/json",
              )
        );
        $sOutput = curl_exec($rCurlHandler);
        $iHTTPCode = curl_getinfo($rCurlHandler, CURLINFO_HTTP_CODE);
        curl_close($rCurlHandler);

        $this->SetData('');

        if ($iHTTPCode !== 204) {
            Log::Write(
                  'WebRequest',
                  'PATCH::ERROR',
                  'HttpCode was ' . $iHTTPCode . '. Expected 204. Response: ' . $sOutput
            );
            throw new InvalidApiResponse('HttpCode was ' . $iHTTPCode . '. Expected 204');
        }
        return $sOutput;
    }

    /**
     * Makes a PUT request to the REST API
     *
     * @return string
     * @throws InvalidApiResponse
     */
    public function Put()
    {
        #HTTP method in uppercase (ie: GET, POST, PUT, DELETE)
        $sMethod = 'PUT';
        $sTimeStamp = gmdate('c');

        #Creating the hash
        $sHashString = implode(
              '|',
              array(
                    $this->GetPublicKey(),
                    $sMethod,
                    $this->GetApiResource(),
                    $this->GetData(),
                    $sTimeStamp,
              )
        );

        $sHash = hash_hmac('sha512', $sHashString, $this->GetSecretKey());

        $rCurlHandler = curl_init();
        curl_setopt($rCurlHandler, CURLOPT_URL, $this->GetApiRoot() . $this->GetApiResource());
        curl_setopt($rCurlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($rCurlHandler, CURLOPT_POSTFIELDS, $this->GetData());
        curl_setopt($rCurlHandler, CURLOPT_CUSTOMREQUEST, $sMethod);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($rCurlHandler, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt(
              $rCurlHandler,
              CURLOPT_HTTPHEADER,
              array(
                    "x-date: " . $sTimeStamp,
                    "x-hash: " . $sHash,
                    "x-public: " . $this->GetPublicKey(),
                    "Content-Type: text/json",
              )
        );
        $sOutput = curl_exec($rCurlHandler);
        $iHTTPCode = curl_getinfo($rCurlHandler, CURLINFO_HTTP_CODE);
        curl_close($rCurlHandler);

        $this->SetData('');

        if ($iHTTPCode !== 204) {
            Log::Write(
                  'WebRequest',
                  'PUT::ERROR',
                  'HttpCode was ' . $iHTTPCode . '. Expected 204. Response: ' . $sOutput
            );
            throw new InvalidApiResponse('HttpCode was ' . $iHTTPCode . '. Expected 204');
        }
        return $sOutput;
    }

    /**
     * The request domain without trailing slash
     *
     * @return string
     */
    public function GetApiResource()
    {
        return $this->ApiResource;
    }

    /**
     * The request domain without trailing slash
     *
     * @param string $ApiResource
     */
    public function SetApiResource($ApiResource)
    {
        $this->ApiResource = $ApiResource;
    }

    /**
     * The request URI minus the domain name
     *
     * @return string
     */
    public function GetApiRoot()
    {
        return $this->ApiRoot;
    }

    /**
     * The request URI minus the domain name
     *
     * @param string $ApiRoot
     */
    public function SetApiRoot($ApiRoot)
    {
        $this->ApiRoot = $ApiRoot;
    }

    /**
     * The data that is being posted to the resource (only with POST or PATCH methods)
     *
     * @return string
     */
    public function GetData()
    {
        return $this->Data;
    }

    /**
     * The data that is being posted to the resource (only with POST or PATCH methods)
     *
     * @param string $Data
     */
    public function SetData($Data)
    {
        $this->Data = JsonSerializer::Serialize($Data);
    }

    /**
     * The "Public key" or "Api key" can be retrieved in the webshop, This should be the same as the header 'x-public'.
     *
     * @return string
     */
    public function GetPublicKey()
    {
        return $this->PublicKey;
    }

    /**
     * The "Public key" or "Api key" can be retrieved in the webshop, This should be the same as the header 'x-public'.
     *
     * @param string $PublicKey
     */
    public function SetPublicKey($PublicKey)
    {
        $this->PublicKey = $PublicKey;
    }

    /**
     * The "Secret key" or "Api secret" can be retrieved in the webshop.
     *
     * @return string
     */
    public function GetSecretKey()
    {
        return $this->SecretKey;
    }

    /**
     * The "Secret key" or "Api secret" can be retrieved in the webshop.
     *
     * @param string $SecretKey
     */
    public function SetSecretKey($SecretKey)
    {
        $this->SecretKey = $SecretKey;
    }
}

<?php

namespace GingerPayments\Payments\Helpers;

use GingerPayments\Payments\PSP\PSPConfig;
use GingerPluginSdk\Client;
use GingerPluginSdk\Properties\ClientOptions;
use GingerPluginSdk\Entities\Order;
use GingerPluginSdk\Exceptions\APIException;

class GingerApiHelper
{
    protected Client $client;

    /**
     * @param string $endpoint
     * @param string $apiKey
     * @throws APIException
     */
    public function __construct(string $endpoint,string $apiKey)
    {
        try { $clientOptions = new ClientOptions(endpoint: $endpoint, useBundle: true, apiKey: $apiKey);
            $this->client = new Client(options: $clientOptions);
        }catch (\Exception $e) {
            throw new APIException("Failed to initialize Ginger API client: " . $e->getMessage(), $e->getCode(), $e);

        }

    }

    /**
     * @param Order $order
     * @return Order
     * @throws APIException
     */
    public function sendOrder(Order $order): Order
    {
        try {
            // Send order to Ginger Payments API
            return $this->client->sendOrder($order);
        } catch (APIException $e) {
            throw new APIException("Error sending order: " . $e->getMessage());
        }
    }
}

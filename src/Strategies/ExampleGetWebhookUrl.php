<?php

namespace GingerPayments\Payments\Strategies;

use GingerPayments\Payments\Interfaces\StrategyInterface\ExampleGetWebhookUrlStrategy;
use OxidEsales\EshopCommunity\Core\Registry;

/**
 * Class ExampleGetWebhookUrl
 *
 * This class implements the ExampleGetWebhookUrlStrategy to generate the webhook URL for a given order.
 */
class ExampleGetWebhookUrl implements ExampleGetWebhookUrlStrategy
{
    /**
     * Generate the webhook URL for a given order.
     *
     * @param string $orderId The ID of the order.
     * @return string The webhook URL.
     */
    public function getWebhookUrl(string $orderId): string
    {
        $shopUrl = Registry::getConfig()->getShopUrl();
        return $shopUrl . "widget.php/?cl=webhook&ox_order=" . $orderId;
    }
}

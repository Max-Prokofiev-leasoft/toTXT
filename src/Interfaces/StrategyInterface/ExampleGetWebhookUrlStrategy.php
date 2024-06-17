<?php

namespace GingerPayments\Payments\Interfaces\StrategyInterface;

/**
 * Interface ExampleGetWebhookUrlStrategy
 *
 * This interface is example for using strategy pattern in plugin.
 **/
interface ExampleGetWebhookUrlStrategy extends BaseStrategy
{
    /**
     * Get the webhook URL for a given order.
     *
     * @param string $orderId The ID of the order.
     * @return string The webhook URL.
     */
    public function getWebhookUrl(string $orderId): string;
}
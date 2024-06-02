<?php

namespace GingerPayments\Payments\Interfaces;

use OxidEsales\EshopCommunity\Application\Model\Order as OxidOrder;

interface BasePaymentInterface
{
    /**
     * Handles the payment process for a given order.
     *
     * @param float $amount
     * Total amount for the order
     * @param OxidOrder $order
     * OXID Order
     * @return string
     * - URL to process payment or payment confirmation
     */
    public function handlePayment(float $amount, OxidOrder $order): string;

}
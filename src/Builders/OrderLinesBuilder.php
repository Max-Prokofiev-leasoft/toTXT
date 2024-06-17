<?php

namespace GingerPayments\Payments\Builders;

use GingerPluginSdk\Collections\OrderLines;
use GingerPluginSdk\Entities\Line;
use GingerPluginSdk\Properties\Amount;
use GingerPluginSdk\Properties\Currency;
use GingerPluginSdk\Properties\RawCost;
use GingerPluginSdk\Properties\VatPercentage;
use OxidEsales\EshopCommunity\Application\Model\Order as OxidOrder;

class OrderLinesBuilder
{
    private OxidOrder $order;

    public function __construct(OxidOrder $order)
    {
        $this->order = $order;
    }

    /**
     * Builds OrderLines object from the given OXID order.
     *
     * @return OrderLines
     * - SDK OrderLines object
     */
    public function buildOrderLines(): OrderLines
    {
        $orderArticles = $this->order->getOrderArticles();
        $lines = [];

        foreach ($orderArticles as $orderArticle) {
            $article = $orderArticle->getArticle();

            $discountRateValue = null;
            if (isset($orderArticle->oxorderarticles__oxdiscount) && $orderArticle->oxorderarticles__oxdiscount->value !== null) {
                $discountRateValue = (new RawCost($orderArticle->oxorderarticles__oxdiscount->value));
            }

            $line = new Line(
                type: 'physical',
                merchantOrderLineId: $orderArticle->getId(),
                name: $article->oxarticles__oxtitle->value,
                quantity: (int)$orderArticle->oxorderarticles__oxamount->value,
                amount: new Amount((new RawCost($orderArticle->oxorderarticles__oxbrutprice->value))),
                vatPercentage: new VatPercentage(value: $orderArticle->oxorderarticles__oxvat->value * 100),
                currency: new Currency($this->order->getOrderCurrency()->name),
                discountRate: $discountRateValue,
                url: $article->getLink()
            );

            $lines[] = $line;
        }

        if ($this->order->oxorder__oxdelcost->value > 0) {
            $lines[] = $this->getShippingOrderLine();
        }

        return new OrderLines(...$lines);
    }

    /**
     * Creates a shipping order line.
     *
     * @return Line
     * - SDK Line object for shipping
     */
    private function getShippingOrderLine(): Line
    {
        $shippingAmount = (float)(
        $this->order->oxorder__oxdelcost->value
        );

        return new Line(
            type: 'shipping_fee',
            merchantOrderLineId: $this->getShippingId(),
            name: 'Shipping',
            quantity: 1,
            amount: new Amount((new RawCost($shippingAmount))),
            vatPercentage: new VatPercentage((int)(0)),
            currency: new Currency($this->order->getOrderCurrency()->name)
        );
    }

    /**
     * Retrieves the shipping name from the order.
     *
     * @return string
     * - Shipping name
     */
    protected function getShippingId(): string
    {
        return preg_replace("/[^A-Za-z0-9 ]/", "", $this->order->oxorder__oxdeltype->value);
    }
}

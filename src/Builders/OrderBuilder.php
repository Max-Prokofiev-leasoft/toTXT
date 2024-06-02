<?php

namespace GingerPayments\Payments\Builders;

use GingerPayments\Payments\Helpers\GingerApiHelper;
use GingerPluginSdk\Collections\Transactions;
use GingerPluginSdk\Entities\Order;
use GingerPluginSdk\Entities\PaymentMethodDetails;
use GingerPluginSdk\Entities\Transaction;
use GingerPluginSdk\Properties\Amount;
use GingerPluginSdk\Properties\Currency;
use OxidEsales\EshopCommunity\Application\Model\Order as OxidOrder;

class OrderBuilder
{
    private float $totalAmount;
    private OxidOrder $order;
    private string $paymentMethod;
    private string $returnUrl;
    private string $webhookUrl;
    private CustomerBuilder $customerBuilder;
    private OrderLinesBuilder $orderLinesBuilder;
    protected GingerApiHelper $gingerApiHelper;

    public function __construct(float $totalAmount, OxidOrder $order, string $paymentMethod, string $returnUrl, string $webhookUrl)
    {
        $this->totalAmount = $totalAmount;
        $this->order = $order;
        $this->paymentMethod = $paymentMethod;
        $this->returnUrl = $returnUrl;
        $this->webhookUrl = $webhookUrl;
        $this->customerBuilder = new CustomerBuilder($order);
        $this->orderLinesBuilder = new OrderLinesBuilder($order);
        $this->gingerApiHelper = GingerApiHelper::getInstance();
    }

    /**
     * Builds an SDK Order object from the given OXID order data.
     *
     * @return Order
     * - SDK order object
     */
    public function buildOrder(): Order
    {
        $paymentMethodDetails = $this->buildPaymentMethodDetails($this->paymentMethod);

        return new Order(
            currency: $this->buildCurrency($this->order),
            amount: $this->buildAmount($this->totalAmount),
            transactions: $this->buildTransactions($this->paymentMethod, $paymentMethodDetails),
            customer: $this->customerBuilder->buildCustomer(),
            orderLines: $this->orderLinesBuilder->buildOrderLines(),
            client: $this->gingerApiHelper->getClientExtra(),
            webhook_url: $this->webhookUrl,
            return_url: $this->returnUrl,
            id: $this->order->getId(),
            merchantOrderId: $this->order->getId(),
            description: $this->buildDescription($this->order),
        );
    }

    /**
     * Builds a Currency object from the given OXID order.
     *
     * @param OxidOrder $order
     * OXID Order
     * @return Currency
     * - SDK Currency object
     */
    private function buildCurrency(OxidOrder $order): Currency
    {
        return new Currency(value: $order->getOrderCurrency()->name);
    }

    /**
     * Builds an Amount object from the given total amount.
     *
     * @param float $totalAmount
     * Total amount
     * @return Amount
     * - SDK Amount object
     */
    private function buildAmount(float $totalAmount): Amount
    {
        return new Amount(value: (int)($totalAmount * 100));
    }

    /**
     * Builds PaymentMethodDetails object if needed.
     *
     * @param string $paymentMethod
     * Payment method name
     * @return PaymentMethodDetails|null
     * - SDK PaymentMethodDetails object or null
     */
    private function buildPaymentMethodDetails(string $paymentMethod): ?PaymentMethodDetails
    {
        if ($paymentMethod === 'ideal') {
            $paymentMethodDetails = new PaymentMethodDetails();
            $paymentMethodDetails->setPaymentMethodDetailsIdeal('');
            return $paymentMethodDetails;
        }
        return null;
    }

    /**
     * Builds Transactions object from the given payment method and details.
     *
     * @param string $paymentMethod
     * Payment method name
     * @param PaymentMethodDetails|null $paymentMethodDetails
     * Payment method details
     * @return Transactions
     * - SDK Transactions object
     */
    private function buildTransactions(string $paymentMethod, ?PaymentMethodDetails $paymentMethodDetails): Transactions
    {
        return new Transactions(new Transaction(paymentMethod: $paymentMethod, paymentMethodDetails: $paymentMethodDetails));
    }

    /**
     * Builds description for the order.
     *
     * @param OxidOrder $order
     * OXID Order
     * @return string
     * - Description string
     */
    private function buildDescription(OxidOrder $order): string
    {
        return "Oxid order " . $order->getId() . " at shop " . $order->getShopId();
    }
}

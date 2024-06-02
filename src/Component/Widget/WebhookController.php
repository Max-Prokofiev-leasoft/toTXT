<?php

namespace GingerPayments\Payments\Component\Widget;

use Exception;
use GingerPayments\Payments\Helpers\GingerApiHelper;
use GingerPayments\Payments\Helpers\PaymentHelper;
use GingerPayments\Payments\PSP\PSPConfig;
use GingerPluginSdk\Entities\Order;
use GingerPluginSdk\Exceptions\APIException;
use JsonException as JsonExceptionAlias;
use OxidEsales\Eshop\Core\WidgetControl;
use OxidEsales\EshopCommunity\Core\Registry;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class WebhookController
 * Handles webhook requests from the Ginger Payments API.
 */
class WebhookController extends WidgetControl
{
    protected GingerApiHelper $gingerApiHelper;
    protected PaymentHelper $paymentHelper;

    /**
     * Constructor to initialize the GingerApiHelper.
     */
    public function __construct()
    {
        parent::__construct();
        require_once PSPConfig::AUTOLOAD_FILE;
        $this->gingerApiHelper = GingerApiHelper::getInstance();
        $this->paymentHelper = new PaymentHelper();

    }

    /**
     * Parent required method to set class key.
     * @return void
     */
    public function setClassKey(): void
    {
    }

    /**
     * Parent required method to set function name.
     * @return void
     */
    public function setFncName(): void
    {
    }

    /**
     * Parent required method to set view parameters.
     * @return void
     */
    public function setViewParameters(): void
    {
    }

    /**
     * Extended parent initialization method.
     * Handles the webhook and returns the result.
     * @return string
     * - Result of handling webhook
     */
    public function init(): string
    {
        try {
            $data = $this->getApiData();
            $orderId = $this->getOrderId();
            $gingerOrder = $this->handleApiOrder(data: $data);
            return $this->handleWebhook(data: $data, orderId: $orderId, gingerOrder: $gingerOrder);
        } catch (JsonExceptionAlias|Exception $e) {
            http_response_code(500);
            return "Internal Server Error: " . $e->getMessage();
        }
    }

    /**
     * Retrieves data from the API request.
     * @return mixed
     * - Data from API request
     * @throws JsonExceptionAlias
     */
    private function getApiData(): mixed
    {
        static $data = null;
        if ($data === null) {
            $input = file_get_contents("php://input");
            $data = json_decode($input, true, 512, JSON_THROW_ON_ERROR);
        }
        return $data;
    }

    /**
     * Retrieves the OXID Order ID from the request parameters.
     * @return string|null
     * - OXID Order ID
     */
    private function getOrderId(): string|null
    {
        return Registry::getRequest()->getRequestParameter('ox_order');
    }

    /**
     * Handles the webhook and updates the OXID order status based on the API data.
     * @param array $data
     * Data from API request
     * @param string $orderId
     * OXID order ID
     * @param Order $gingerOrder
     * Ginger order
     * @return int
     * - Webhook response status
     */
    private function handleWebhook(array $data, string $orderId, Order $gingerOrder): int
    {
        if (!$orderId) {
            http_response_code(404);
            return print " Order ID is missing";
        }

        if ($data['event'] !== "transaction_status_changed") {
            http_response_code(400);
            return print " Event is not transaction_status_changed";
        }

        $apiOrderStatus = $gingerOrder->getStatus()->get();
        $oxidOrderStatus = $this->paymentHelper->mapApiStatus(apiStatus:  $apiOrderStatus);

        $order = oxNew(\oxorder::class);
        if ($order->load($orderId)) {
            $order->oxorder__oxtransstatus = new \OxidEsales\Eshop\Core\Field($oxidOrderStatus);
            switch ($oxidOrderStatus) {
                case 'EXPIRED':
                case 'CANCELLED':
                    $order->oxorder__oxstorno = new \OxidEsales\Eshop\Core\Field(1);
                    break;
                case 'PAID':
                    $order->oxorder__oxpaid = new \OxidEsales\Eshop\Core\Field(date('Y-m-d H:i:s'));
                    break;
            }
            $order->save();

            $newStatus = $order->oxorder__oxtransstatus->value;
            http_response_code(200);
            return print " Order status updated successfully to $newStatus";
        }
        http_response_code(404);
        return print " Order not found";
    }


    /**
     * Retrieves the Ginger order based on the API data.
     * @param array $data
     * Data from API request
     * @return Order
     * - SDK Order
     * @throws Exception
     */
    private function handleApiOrder(array $data): Order
    {
        $gingerOrderId = $data['order_id'] ?? null;
        if (!$gingerOrderId) {
            throw new \RuntimeException("Order ID is missing in the API data.");
        }
        return $this->gingerApiHelper->getOrder(orderId: $gingerOrderId);
    }
}


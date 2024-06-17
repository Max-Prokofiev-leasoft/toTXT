<?php

namespace GingerPayments\Payments\Component\Widget;

use GingerPayments\Payments\Helpers\PaymentHelper;
use OxidEsales\Eshop\Core\WidgetControl;
use OxidEsales\EshopCommunity\Core\Registry;

class FailedPaymentController extends WidgetControl
{
    /**
     * Constructor to initialize the GingerApiHelper.
     */
    public function __construct()
    {
        parent::__construct();

        // Output the styled error message
        echo $this->getStyledErrorMessage();
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
     * @return string|null - Result of handling webhook
     */
    public function init(): ?string
    {
        return 1;
    }

    /**
     * Get the checkout URL.
     * @return string
     */
    private function getCheckoutUrl(): string
    {
        return Registry::getConfig()->getShopUrl();
    }

    /**
     * Get the styled error message HTML.
     * @return string
     */
    private function getStyledErrorMessage(): string
    {
        return "
            <div style='text-align: center; padding: 50px;'>
                <div style='display: inline-block; text-align: left; max-width: 600px; width: 100%;'>
                    <h1 style='color: #d9534f;'>Payment Failed</h1>
                    <p style='color: #5f5f5f;'>Sorry, we can't proceed with your payment right now. Please try again later.</p>
                    <p style='color: #5f5f5f;'>You will be redirected to the checkout page in a few seconds.</p>
                </div>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = '" . $this->getCheckoutUrl() . "';
                }, 3000);
            </script>
            <style>
                body {
                    background-color: #f8f9fa;
                    font-family: Arial, sans-serif;
                }
            </style>
        ";
    }
}

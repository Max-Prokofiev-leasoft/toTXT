<?php

namespace GingerPlugin\Components\Classes;

use GingerPlugins\Components\Classes\Helper;
use GingerPlugins\Components\Classes\OrderBuilder;
use GingerPlugins\Components\Configurators\BankConfig;
use GingerPlugins\Data\Data_Credential;
use GingerPlugins\Data\Translations;
use GingerPlugins\Exceptions\InvalidHashException;
use GingerPlugins\Http\Hash;
use GingerPlugins\Log\Log;
use GingerPlugins\Components\Traits\AppConnectorTrait;
use GingerPlugins\Services\PaymentMethodsRepository\PaymentMethodsCollection;

class PluginGateway extends OrderBuilder
{
    use AppConnectorTrait;

    /**
     * @throws \GingerPlugins\Exceptions\InvalidApiResponse
     * @throws \Exception
     */
    public function __construct($method, $credential)
    {
        if (is_null(BankConfig::AppSecretKey)) {
            throw new \Exception('AppSecretKey is empty. Please config BankConfig.php');
        }

        if (is_null(BankConfig::AppHandshakeUri)) {
            throw new \Exception('AppHandshakeUri is empty. Please config BankConfig.php');
        }

        if (is_null(BankConfig::AppUninstallUri)) {
            throw new \Exception('AppUnInstallUri is empty. Please config BankConfig.php');
        }
        parent::__construct($method, $credential);
    }

    /**
     * Step 1. Handshake Endpoint
     * The Handshake is the first step in installing the application. The webshop send the initial credentials to the Handshake Endpoint.
     * When the Handshake is successful and this page returns a HTTP 200 OK, the user will be forwarded to the Install Endpoint (step 2).
     */
    public function handshake()
    {
        try {
            Log::WriteStartCall(__FILE__);
            Log::Write('Handshake', 'INPUT', @file_get_contents('php://input'));

            $this->ProcessCredentials();

            Log::Write('Handshake', 'OUTPUT', 'HTTP/1.1 200 OK');
            Log::WriteEndCall(__FILE__);

            header('HTTP/1.1 200 OK', true, 200);
            die('OK');
        } catch (\Exception $oEx) {

            Log::Write('Handshake', 'ERROR', 'HTTP/1.1 500 Internal Server Error. ' . $oEx->getMessage());
            Log::WriteEndCall(__FILE__);

            header('HTTP/1.1 500 Internal Server Error', true, 500);
            die($oEx->getMessage());
        }
    }

    /**
     * Step 2. Install Endpoint
     * The install step is the location where the user will be ask to confirm his account and set any settings associated with this app.
     * You can look up the credentials given in the step 1 handshake with the api_public in the uri.
     * You are free to design this process in any way needed. Once the user completes his installation process,
     * he should be forwarded to the Return URL given in the handshake.
     * Make sure you mark the app as 'installed' before forwarding the user.
     */
    public function initiate_install()
    {
        /**
         * Some minor validation if the input is indeed a string.
         * It's advised to store this in a session for instances. For demo purposes we'll leave this in the request.
         */
        $api_public = filter_input(INPUT_GET, 'api_public', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $_GET['api_public'] = (isset($_GET['api_public']) && is_string($_GET['api_public'])) ? filter_input(INPUT_GET,'api_public', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
        try {
            Log::WriteStartCall(__FILE__);
            $xHash = filter_input(INPUT_GET, 'x-hash', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $language = filter_input(INPUT_GET, 'language', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            #First visit
            $oHash = new Hash();
            $bValid = $oHash->AddData(BankConfig::AppInstallUri)->AddData($api_public)->IsValid($xHash);

            if ($bValid === false) {
                throw new InvalidHashException();
            }

            $ginger_api_key = '';
            $ginger_afterpay_test_api_key = '';
            $ginger_klarna_test_api_key = '';
            $ginger_afterpay_countries = '';

            $credential = !empty($api_public) ? Data_Credential::GetOneByApiPublic($api_public) : null;
            if (!is_null($credential)) {
                if ($credential->GetGingerApiKey()) $ginger_api_key = $credential->GetGingerApiKey();
                if ($credential->GetGingerAfterpayTestApiKey()) $ginger_afterpay_test_api_key = $credential->GetGingerAfterpayTestApiKey();
                if ($credential->GetGingerKlarnaTestApiKey()) $ginger_klarna_test_api_key = $credential->GetGingerKlarnaTestApiKey();
                if ($credential->GetGingerAfterpayCountries()) $ginger_afterpay_countries = $credential->GetGingerAfterpayCountries();
            }
            $gingerTranslations = Translations::getTranslations($language);
            $paymentMethodsCollection = new PaymentMethodsCollection();
            $paymentMethods = $paymentMethodsCollection->getPaymentCollection();

            include_once __DIR__ . "/../View/install.php";
        } catch (\Exception $oEx) {

            Log::Write('Install', 'ERROR', 'HTTP/1.1 500 Internal Server Error. ' . $oEx->getMessage());
            Log::WriteEndCall();

            header('HTTP/1.1 500 Internal Server Error', true, 500);
            die($oEx->getMessage());
        }
    }

    public function finish_install()
    {
        try {
            // Install app when clicked Install button with $_POST data
            $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $this->EditCredential();

            if ($action == 'install') {
                $this->Install();
            }

            $this->addIssuersToIdeal();

            Log::Write('Install', 'OUTPUT', 'Location: ' . $this->GetCredential()->GetReturnUrl());
            Log::WriteEndCall(__FILE__);


            header('Location: ' . $this->GetCredential()->GetReturnUrl());
            header('HTTP/1.1 200 OK', true, 200);
            die(200);

        } catch (\Exception $oEx) {
            Log::WriteStartCall(__FILE__);
            Log::Write('Install', 'ERROR', 'HTTP/1.1 500 Internal Server Error. ' . $oEx->getMessage());
            Log::WriteEndCall();

            header('HTTP/1.1 500 Internal Server Error', true, 500);
            echo $oEx->getMessage();
            die();
        }
    }

    /**
     * Step 3. UnInstall Endpoint
     * Whenever the user uninstalls the app in his webshop, the UnInstall Endpoint will be called.
     * This will give you the option to process the uninstall.
     */
    public function initiate_uninstall()
    {
        $this->UnInstall();
    }
}
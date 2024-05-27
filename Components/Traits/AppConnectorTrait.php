<?php

namespace GingerPlugins\Components\Traits;

use GingerPlugin\Components\Classes\Redefiner;
use GingerPlugins\Components\Configurators\BankConfig;
use GingerPlugins\Components\Traits\WebRequestTrait;
use GingerPlugins\Components\Classes\Helper;
use GingerPlugins\Exceptions\InvalidApiResponse;
use GingerPlugins\Exceptions\InvalidCredentialException;
use GingerPlugins\Exceptions\InvalidHashException;
use GingerPlugins\Exceptions\InvalidJsonException;
use GingerPlugins\Http\WebRequest;
use GingerPlugins\Json\JsonSerializer;
use GingerPlugins\Data\Data_Credential;
use GingerPlugins\Entities\Credential;
use GingerPlugins\Http\Hash;
use GingerPlugins\Log\Log;
use GingerPlugins\Services\PaymentMethodsRepository\PaymentMethodsCollection;

trait AppConnectorTrait
{
    use WebRequestTrait;

    /**
     * @var Credential Credential Contains the credentials. Used for example purposes only
     */
    protected $Credential;

    /**
     * @var null|int Contains the ID
     */
    protected $RemoteAppId = null;

    /**
     * Processes the handshake. The app store will send JSON containing api credentials.
     * These credentials will be needed further in the process.
     *
     * @throws InvalidHashException
     * @throws InvalidJsonException
     */
    public function ProcessCredentials()
    {
        Log::Write(__FUNCTION__, 'START');
        $this->ValidateHash(BankConfig::AppHandshakeUri);

        $oData = JsonSerializer::DeSerialize(@file_get_contents('php://input'));

        $this->Credential = new Credential($oData);
        Data_Credential::Insert($this->Credential);

        Log::Write(__FUNCTION__, 'END');
    }

    /**
     * Create webhook for CCV Shop update order status
     * @throws \GingerPlugins\Exceptions\InvalidApiResponse|\GingerPlugins\Exceptions\InvalidJsonException
     */
    protected function CreateWebhook()
    {
        $output = $this->makeWebRequest('webshops/', 'GET');


        $aCollectionOfApps = \GingerPlugins\Json\JsonSerializer::DeSerialize($output);

        if (!isset($aCollectionOfApps->items)) {
            throw new InvalidApiResponse('Collection contained zero apps. Expected 1.');
        }

        if (count($aCollectionOfApps->items) > 1) {
            throw new InvalidApiResponse(
                'Collection contained ' . count($aCollectionOfApps->items) . ' apps. Expected 1.'
            );
        }

        $storeId = $aCollectionOfApps->items[0]->id;

        $webhook = new \stdClass();
        $webhook->event = 'orders.updated.status';
        $webhook->address = BankConfig::AppUri . "/order_status_changed?storeId=" . $storeId;
        $webhook->is_active = true;

        $this->makeWebRequest('webhooks', 'POST', $webhook);
    }

    /**
     * Once the customer has successfully filled in the form, we proceed with the installation.
     * Creating the needed WebHooks in the webshop and marking the app as installed.
     *
     * @throws InvalidApiResponse
     * @throws InvalidCredentialException
     */
    public function Install()
    {
        if ($_REQUEST['install_type'] == 'app_psp') {
            $this->install_psp();
        }

        // Marking the app as installed (MANDATORY).
        $this->Install_App();

        // Add webhook for Shipped order status
        $this->CreateWebhook();
    }

    /**
     * Creates the webhooks in the webshop.
     *
     * @throws InvalidJsonException
     */
    protected function Install_WebHooks()
    {
        $oWebRequest = new WebRequest();
        $oWebRequest->SetPublicKey($this->Credential->GetApiPublic());
        $oWebRequest->SetSecretKey($this->Credential->GetApiSecret());
        $oWebRequest->SetApiRoot($this->Credential->GetApiRoot());
        $oWebRequest->SetApiResource('/api/rest/v1/webhooks');

        #These webhooks will be created in the webshop. When the event is triggered a payload will be posted to the address.
        $aWebHooksToInstall = [];
        $aWebHooksToInstall[] = (object)['event' => 'products.created', 'address' => 'https://demo.securearea.eu/void.php'];
        $aWebHooksToInstall[] = (object)['event' => 'products.updated', 'address' => 'https://demo.securearea.eu/void.php'];
        $aWebHooksToInstall[] = (object)['event' => 'products.deleted', 'address' => 'https://demo.securearea.eu/void.php'];

        foreach ($aWebHooksToInstall as $oData) {
            $oWebRequest->SetData($oData);
            $sOutput = $oWebRequest->Post();

            $oWebHook = new WebHook(JsonSerializer::DeSerialize($sOutput));
            $oWebHook->SetCustomerId($this->Credential->GetCustomerId());

            #Store WebHook keys
            Data_WebHook::Insert($oWebHook);
        }
    }

    /**
     * @throws \Exception
     */
    protected function install_psp()
    {
        $iAppId = $this->getAppId();

        #Marking app as 'installed'
        $app = new \stdClass();
        $app->name = BankConfig::BANK_LABEL;
        $app->description = 'Pay using ' . BankConfig::BANK_LABEL;
        $app->endpoint = BankConfig::AppUri;
        $app->icon = implode('/', [
            BankConfig::AppUri,
            BankConfig::IMAGE_FOLDER,
            'plugin.png'
        ]);

        $payment_collection = new PaymentMethodsCollection();
        $app->paymethods = $payment_collection->getPaymentCollection();

        $this->makeWebRequest('apps/' . $iAppId . '/apppsp/', 'POST', $app);
        $this->addIssuersToIdeal($iAppId);

    }

    /**
     * Add Issuers to the iDeal payment method from the Ginger API
     *
     * @param string $iAppId
     * @param Credential $credentials
     *
     * @throws \GingerPlugins\Exceptions\InvalidApiResponse|\GingerPlugins\Exceptions\InvalidCredentialException
     * @throws \Exception
     */
    public function addIssuersToIdeal($iAppId = null, $credentials = null)
    {
        if (!$iAppId) {
            $iAppId = $this->getAppId();
        }

        $appInfo = $this->makeWebRequest('apps/' . $iAppId . '/apppsp/', 'GET');

        if (is_null($credentials)) {
            $credentials = $this->Credential;
        }

        $ginger_issuers = $this->getIssuers();
        $issuers = [];
        foreach ($ginger_issuers as $issuer) {
            $oIssuer = new \stdClass();
            $oIssuer->id = $issuer['id'];
            $oIssuer->name = $issuer['name'];
            $issuers[] = $oIssuer;
        }
        $appInfo = json_decode($appInfo);

        $appIndex = 0;
        foreach ($appInfo->items as $iIndex => $item) {
            foreach ($item->paymethods as $pIndex => $method) {
                if ($method->id == 'ideal') {
                    $appInfo->items[$iIndex]->paymethods[$pIndex]->issuers = $issuers;
                    $appIndex = $iIndex;
                    break;
                }
            }
        }

        $appObj = new \stdClass();
        $appObj->paymethods = $appInfo->items[$appIndex]->paymethods;

        $this->makeWebRequest('apppsp/' . $appInfo->items[$appIndex]->id, 'PATCH', $appObj);
    }


    /**
     * Update Credentials
     */
    protected function EditCredential()
    {
        $sApiPublic = $_REQUEST['api_public'];
        $this->Credential = Data_Credential::GetOneByApiPublic($sApiPublic);
        $this->Credential->SetGingerApiKey($_REQUEST['ginger_api_key']);
        $this->Credential->SetGingerAfterpayTestApiKey($_REQUEST['ginger_afterpay_test_api_key']);
        $this->Credential->SetGingerKlarnaTestApiKey($_REQUEST['ginger_klarna_test_api_key']);
        $this->Credential->SetGingerAfterpayCountries($_REQUEST['ginger_afterpay_countries']);
        Data_Credential::Update($this->Credential);
    }

    /**
     * Mandatory.
     * Calls the API and retrieves the App.Id associated with the api_public.
     * After that a Patch is send to update the app.is_installed property, marking it as installed.
     *
     * @throws InvalidApiResponse
     * @throws InvalidJsonException
     */
    protected function Install_App()
    {
        #Marking app as 'installed'
        $app = new \stdClass();
        $app->is_installed = true;

        $app_id = $this->GetRemoteAppId();

        $this->makeWebRequest(
            'apps/' . $app_id,
            'PATCH',
            $app
        );
    }

    /**
     * Optional.
     * Just clears up some of the local data files.
     *
     * @throws InvalidCredentialException
     * @throws InvalidHashException
     * @throws
     */
    public function UnInstall()
    {
        $this->ValidateHash(BankConfig::AppUninstallUri);

        $oPostedData = JsonSerializer::DeSerialize(@file_get_contents('php://input'));
        $this->Credential = Data_Credential::GetOneByApiPublic($oPostedData->api_public);

        Data_Credential::Delete($this->GetCredential());
    }

    /**
     * @return Credential
     * @throws InvalidCredentialException
     */
    public function GetCredential(): Credential
    {
        if (!is_a($this->Credential, 'GingerPlugins\Entities\Credential')) {
            throw new InvalidCredentialException();
        }
        return $this->Credential;
    }

    /**
     * Validates the hash in the header with the calculated hash. Check data integrity.
     * @param $sUri
     *
     * @throws InvalidHashException
     * @author Olexandr Tiutiunnyk
     *
     */
    protected function ValidateHash($sUri)
    {
        $apache_request_headers = apache_request_headers();

        $data_array = [];
        $data_array[] = $sUri;
        $data_array[] = @file_get_contents('php://input');
        $data_hash_string = implode('|', $data_array);
        $hash = hash_hmac('sha512', $data_hash_string, BankConfig::AppSecretKey);

        if ($hash != $apache_request_headers[BankConfig::HeaderXHash]) {
            throw new InvalidHashException();
        }
    }

    protected function GetRemoteAppId()
    {
        if (is_null($this->RemoteAppId)) {
            $oWebRequest = new WebRequest();
            #Getting Remote App resource
            $oWebRequest->SetPublicKey($this->Credential->GetApiPublic());
            $oWebRequest->SetSecretKey($this->Credential->GetApiSecret());
            $oWebRequest->SetApiRoot($this->Credential->GetApiRoot());
            $oWebRequest->SetApiResource('/api/rest/v1/apps');
            $sOutput = $oWebRequest->Get();

            $aCollectionOfApps = JsonSerializer::DeSerialize($sOutput);

            if (!isset($aCollectionOfApps->items)) {
                throw new InvalidApiResponse('Collection contained zero apps. Expected 1.');
            }

            if (count($aCollectionOfApps->items) > 1) {
                throw new InvalidApiResponse('Collection contained ' . count($aCollectionOfApps->items) . ' apps. Expected 1.');
            }

            $this->RemoteAppId = $aCollectionOfApps->items[0]->id;
        }

        return $this->RemoteAppId;
    }
}

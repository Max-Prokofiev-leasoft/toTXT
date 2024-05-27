<?php

use GingerPlugins\Components\Configurators\BankConfig;
use GingerPlugins\Http\Hash;
use GingerPlugins\Json\JsonSerializer;
use GingerPlugins\Log\Log;
use GingerPlugins\Services\TransactionRepository\TransactionCollection;

try {
    require_once __DIR__ . '/../TransactionRepository/TransactionCollection.php';
    $oTransactionFactory = new TransactionCollection();

    Log::WriteStartCall(__FILE__);
    $sResponse = $oTransactionFactory->GetStatus(filter_input(INPUT_GET,'transaction_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    echo $sResponse;
    Log::WriteEndCall(__FILE__);
    die();


} catch (\Exception $oEx) {
    Log::Write('Endpoint', 'ERROR', 'HTTP/1.1 500 Internal Server Error. ' . $oEx->getMessage());
    Log::WriteEndCall(__FILE__);

    header('HTTP/1.1 500 Internal Server Error', true, 500);
    $oOutput = new \stdClass();
    $oOutput->status = 'FAILED';
    $oOutput->error = $oEx->getMessage();

    $sResponse = JsonSerializer::Serialize($oOutput);
    $oHash = new Hash();
    $sHash = $oHash->AddData(BankConfig::AppUri . $_SERVER['REQUEST_URI'])->AddData($sResponse)->Hash();

    echo $sResponse;
    die();
}

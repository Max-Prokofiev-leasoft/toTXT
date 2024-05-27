<?php

use GingerPlugins\Services\TransactionRepository\TransactionCollection;

require_once __DIR__.'/../TransactionRepository/TransactionCollection.php';

$transaction_collection = new TransactionCollection();
$transaction_collection->post();
?>
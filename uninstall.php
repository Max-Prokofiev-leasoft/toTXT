<?php
require_once 'autoloader.php';

use GingerPlugin\Components\Classes\Redefiner;
use GingerPlugins\Components\Classes\Helper;

\GingerPlugins\Log\Log::Write('BeforeUpdate','DEBUG', @file_get_contents('php://input'));

$plugin_gateway = new Redefiner(null, Helper::getCredentials());
$plugin_gateway->initiate_uninstall();
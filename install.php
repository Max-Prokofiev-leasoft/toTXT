<?php
require_once 'autoloader.php';

use GingerPlugin\Components\Classes\Redefiner;
use GingerPlugins\Components\Classes\Helper;

\GingerPlugins\Log\Log::Write('BeforeInstall', 'DEBUG', @file_get_contents('php://input'));
$plugin_gateway = new Redefiner(Helper::getCredentials());
if (empty($_POST)) {
    $plugin_gateway->initiate_install();
} else {
    $plugin_gateway->finish_install();
}
<?php
require_once './local/vendor/autoload.php';

$api = new \App\EthereumApi();

$api->sendContract();

<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/../local/vendor/autoload.php';
$app = AppFactory::create();

$app->setBasePath('/api');

$app->post('/addCertificate/', '\App\Api::addCertificate');
$app->post('/checkCertificate/', '\App\Api::checkApi');

$app->run();
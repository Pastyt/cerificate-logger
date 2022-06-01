<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../local/vendor/autoload.php';
$app = AppFactory::create();

$app->setBasePath('/api');

$app->get('/checkApi/', '\App\Api::checkApi');

$app->run();
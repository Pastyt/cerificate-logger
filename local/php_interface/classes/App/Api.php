<?php

namespace App;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Api
{
    static function checkApi(Request $request, Response $response)
    {

        $params = $request->getQueryParams();

        $response->getBody()->write(json_encode($params));

        return $response;
    }
}
<?php

namespace App;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


class Api
{
    /**
     * $req['domain'] = '' $req['sing'] = ''
     * @param  Request   $request
     * @param  Response  $response
     *
     * @return Response
     */
    static function addCertificate(Request $request, Response $response)
    {
        $params = $request->getQueryParams();

        if (openssl_verify('oreluniver.ru','$sign',$pubKey) !== 1) {
            $response->getBody()->write(json_encode(['error' => true], JSON_THROW_ON_ERROR));
        }

        $url = $params['domain'];

        //Парсинг домена и получение его сертификата
        $orignal_parse = parse_url($url, PHP_URL_HOST);
        $get = stream_context_create(['ssl' => ['capture_peer_cert' => true]]);
        $read = stream_socket_client(
            'ssl://' . $orignal_parse . ':443',
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $get
        );

        $cert = stream_context_get_params($read);
        $parsed_cert = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
        //Проверка сертификата на валидность
        //Сохраняем сертификат в IPFS (для периодической проверки OSCP)
        //Кладем в блокчейн serialNumber и validTo и хэш с IPFS
        //Если все успех то возвращаем успех
        $response->getBody()->write(json_encode(''));

        return $response;
    }
}
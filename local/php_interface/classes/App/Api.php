<?php
namespace App;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


class Api
{
    /**
     * $req['domain'] = '' $req['sing'] = ''  $req['time'] = ''
     * @param  Request   $request
     * @param  Response  $response
     *
     * @return Response
     */
    public static function addCertificate(Request $request, Response $response)
    {
        $params = $request->getQueryParams();

        //Отправить запрос на сервер может только владелец закрытого ключа доменов, параметр времени необходим для
        //невозможности дублировать запрос
//        if ($params['time'] < time() && openssl_verify($params['domain'] . $params['time'],$params['sign'],$pubKey) !== 1) {
//            $response->getBody()->write(json_encode(['error' => true], JSON_THROW_ON_ERROR));
//            return $response;
//        }

        $url = $params['domain'];

        //Парсинг домена и получение его цепочки сертификатов
        $orignal_parse = parse_url($url, PHP_URL_HOST);

        $get = stream_context_create(
            [
                'ssl' => [
                    'capture_peer_cert_chain' => true,
                    //С проверкой сертификатов
                    'verify_peer'       => true,
                    'verify_peer_name'  => true,
                ],
            ]
        );

        $read = @stream_socket_client(
            'ssl://' . $orignal_parse . ':443',
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $get
        );

        //Обработка в случае если сертификат недействителен
        if ($err = error_get_last()) {
            $response->getBody()->write(json_encode(['error' => $err['message']], JSON_THROW_ON_ERROR));
            return $response;
        }

        $cert = stream_context_get_params($read);

        $ipfs = new IPFSApi();

        if (!openssl_x509_export($cert['options']['ssl']['peer_certificate_chain'][0], $certificate)
            || !openssl_x509_export($cert['options']['ssl']['peer_certificate_chain'][1], $issuer_certificate)
        ) {
            $response->getBody()->write(json_encode(['error' => 'export fail'], JSON_THROW_ON_ERROR));
            return $response;
        }

        $hash = $ipfs->addCertToIPFS($certificate);
        $issuer_hash = $ipfs->addCertToIPFS($issuer_certificate);

        //Проверка сертификата на валидность / уже наличие сертификата в блокчейне


        //Сохраняем сертификат в IPFS (для периодической проверки OSCP)
        //Кладем в блокчейн serialNumber и validTo и хэш с IPFS
        //Если все успех то возвращаем успех
        $response->getBody()->write(json_encode(''));

        return $response;
    }
    public static function checkCertificate(Request $request, Response $response)
    {
        //Проверка статуса сертификата
        //Если сертификат недействителен уведомить соответствующий домен о невалидном входе
    }

    public static function sendMessage()
    {
        //Отправка определенного сообщения на api сервера
    }


}
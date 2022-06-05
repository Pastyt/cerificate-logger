<?php
namespace App;

use Cloutier\PhpIpfsApi\IPFS;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


class Api
{
    private const PYTHON_RUN = 'python.exe ./local/python/ethereumApi.py';

    /**
     * $req['domain'] = '' $req['sing'] = ''  $req['nonce'] = ''
     * @param  Request   $request
     * @param  Response  $response
     *
     * @return Response
     */
    public static function addCertificate(Request $request, Response $response) : Response
    {
        $params = $request->getQueryParams();
        //nonce true или false в зависимости от последнего nonce, находящегося в блокчейне

        //Ограничение запроса, он обработается только если отправитель это владелец закрытого ключа доменов
        if (openssl_verify($params['domain'] . $params['time'],$params['sign'],$pubKey) !== 1) {
            return self::sendError($response,'wrong sign');
        }

        $url = $params['domain'];

        $cert = Certificate::getCertForDomain($url);

        if (is_array($cert) && $cert['error']) {
            self::sendError($response,$cert['error']);
        }

        //Проверка на наличие серийного номера в блокчейне
        $cert_info = openssl_x509_parse($cert['options']['ssl']['peer_certificate_chain'][0]);
        $issuer_cert_info = openssl_x509_parse($cert['options']['ssl']['peer_certificate_chain'][1]);

        //true или false в зависимости от наличия в блокчейне
        $cert_check = EthereumApi::checkCert($cert_info['serialNumber']);

        if ($cert_check[0]) {
            return self::sendError($response,'already in blockchain');
        }

        if (!openssl_x509_export($cert['options']['ssl']['peer_certificate_chain'][0], $certificate)
            || !openssl_x509_export($cert['options']['ssl']['peer_certificate_chain'][1], $issuer_certificate)
        ) {
            return self::sendError($response,'error when export');
        }

        $ipfs = new IPFSApi();
        $hash = $ipfs->addCertToIPFS($certificate);
        $issuer_hash = $ipfs->addCertToIPFS($issuer_certificate);

        $contract_params = [
            'serial' => $cert_info['serialNumber'],
            'issuer_serial' => $issuer_cert_info['serialNumber'],
            'expiry' => $cert_info['validTo_time_t'],
            'hash' => $hash,
            'issuer_hash' => $issuer_hash,
        ];

        unset($serial,$issuer_serial,$hash,$issuer_hash,$cert);

        $output = EthereumApi::sendCertToBlockchain($contract_params);

        if ($output[0] !== 'true') {
            return self::sendError($response,'error with sending transaction into blockchain');
        }

        $response->getBody()->write(json_encode(['success' => true, 'nonce' => $params['nonce'] + 1]));

        return $response;
    }

    /**
     * @param  Request   $request $request['certId'] = '' , $request['nonce'] = '',
     * @param  Response  $response
     *
     * @return Response
     */
    public static function checkCertificate(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        //Возвращает хэши сохраненных сертификатов если они не отозваны в блокчейне и false в ином случае
        $output = EthereumApi::getCertFromBlockchain($params['certId']);

        if ($output[0] === 'false') {
            return self::sendError($response, 'not found/revoked');
        }
        $ipfs = new IPFSApi();

        $cert = $ipfs->getCertFromIpfs($output[0]);
        $issuer_cert = $ipfs->getCertFromIpfs($output[1]);

        //Проверка истечения
        $certinfo = openssl_x509_parse($cert);
        if ($certinfo['validFrom_time_t'] > time() || $certinfo['validTo_time_t'] < time()) {
            return self::sendError(
                $response,
                'expired',
                ['domain' => $certinfo['subject']['CN'], 'certId' => $certinfo['serialNumber']]
            );
        }

        //Проверка OSCP
        if (!Certificate::getOSCPResult($cert, $issuer_cert)) {
            return self::sendError(
                $response,
                'revoked by crl',
                ['domain' => $certinfo['subject']['CN'], 'certId' => $certinfo['serialNumber']]
            );
        }

        $response->getBody()->write(json_encode(['success' => true, 'nonce' => $params['nonce'] + 1]));

        return $response;
    }

    private static function sendError(Response $response, string $error, array $params = []) : Response
    {
        if ($params) {
            $url = 'https://'. $params['domain'] .'/api/loggerError/';
            $data = ['error' => $error, 'certId' => $params['certId']];

            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                ],
            ];
            $context = stream_context_create($options);
            //TODO Логирование ошибки
            $result = file_get_contents($url, false, $context);
        }

        $response->getBody()->write(json_encode(['success' => false, 'error' => $error, 'nonce' => -1], JSON_THROW_ON_ERROR));
        return  $response;
    }


}
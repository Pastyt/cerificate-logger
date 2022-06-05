<?php

namespace App;

class Certificate
{

    /**
     * Парсинг домена и получение его цепочки сертификатов
     * @param  string  $domain
     *
     * @return array|null
     */
    public static function getCertForDomain(string $domain)
    {

        $orignal_parse = parse_url($domain, PHP_URL_HOST);

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
            return ['error' => $err];
        }

        $cert_chain = stream_context_get_params($read);
        return $cert_chain;
    }

    /**
     * Получить информацию о отозванности сертификата в OSCP
     * @param  string  $cert строчка, содержащая сертификат, к примеру через openssl_x509_export()
     * @param  string  $issuer_cert строчка, содержащая сертификат, к примеру через openssl_x509_export()
     *
     * @return \Ocsp\Response
     */
    public static function getOSCPResult(string $cert, string $issuer_cert) : ?bool
    {
        $certificateLoader = new \Ocsp\CertificateLoader();
        $certificateInfo = new \Ocsp\CertificateInfo();
        $ocsp = new \Ocsp\Ocsp();

        //Загрузка сертификатов с подготовкой запроса к OSCP
        $certificate = $certificateLoader->fromString($cert);
        $issuerCertificate = $certificateLoader->fromString($issuer_cert);
        $ocspResponderUrl = $certificateInfo->extractOcspResponderUrl($certificate);
        $requestInfo = $certificateInfo->extractRequestInfo($certificate, $issuerCertificate);

        $requestBody = $ocsp->buildOcspRequestBodySingle($requestInfo);


        $options = array(
            'http' => array(
                'header'  => 'Content-Type: ' . \Ocsp\Ocsp::OCSP_REQUEST_MEDIATYPE,
                'method'  => 'POST',
                'content' => $requestBody
            )
        );
        $context  = stream_context_create($options);
        if (!$result = file_get_contents($ocspResponderUrl, false, $context))
            return null;

        //Обработка запроса
        $response = $ocsp->decodeOcspResponseSingle($result);
        return $response->isRevoked();
    }
}
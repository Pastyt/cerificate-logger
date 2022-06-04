<?php
//Получить массив хэшей IPFS действительных сертификатов с блокчейна
//Скачать сертификаты, проверить их все на действительность через OSCP,
// если сертификат поменял статус отправить информацию домену

/**
 * Получить информацию о отозванности сертификата в OSCP
 * @param  string  $cert строчка, содержащая сертификат, к примеру через openssl_x509_export()
 * @param  string  $issuer_cert строчка, содержащая сертификат, к примеру через openssl_x509_export()
 *
 * @return \Ocsp\Response
 */
function getOSCPResult(string $cert, string $issuer_cert) : ?bool
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
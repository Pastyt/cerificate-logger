<?php

namespace App;

//use Web3\Contract;
//use Web3\Personal;
//use Web3\Web3;
//use Web3\Eth;
//use Web3\Providers\HttpProvider;
//use Web3\RequestManagers\HttpRequestManager;
class EthereumApi
{

    private const PYTHON_RUN = 'python.exe ./local/python/ethereumApi.py';

    public static function checkCert(string $serialNumber): array
    {
        exec(self::PYTHON_RUN . ' ' . 'checkCert '. $serialNumber,$output);
        return $output;

    }

    public static function sendCertToBlockchain(array $params): array
    {
        exec(self::PYTHON_RUN . ' ' . 'sendCertToBlockchain '. json_encode($params, JSON_THROW_ON_ERROR),$output);
        return $output;
    }

    public static function getCertFromBlockchain($certId): array
    {
        exec(self::PYTHON_RUN . ' ' . 'getCertFromBlockchain '. $certId,$output);
        return $output;
    }
//        exec(self::PYTHON_RUN . ' ' . 'checkNonce '. $params['nonce'],$nonce);

//    private Web3 $web3;
//    private Personal $personal;
//    private Eth $eth;

//    public function __construct()
//    {
//        $this->web3 = new Web3(new HttpProvider(new HttpRequestManager('https://ropsten.infura.io/v3/bb808692392c4e6daf797b961b13848e')));
//        $this->personal = $this->web3->getPersonal();
//        $this->eth = $this->web3->getEth();
//    }
//
//    public function sendContract()
//    {
//        $this->eth->blockNumber(function ($err, $data) {
//            echo 'Latest block number is: ' . $data . " \n";
//        });
//        $eth = $this->web3->eth;
//
//    }

}
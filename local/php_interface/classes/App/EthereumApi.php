<?php

namespace App;

use Web3\Contract;
use Web3\Personal;
use Web3\Web3;
use Web3\Eth;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
class EthereumApi
{
    private Web3 $web3;
    private Personal $personal;
    private Eth $eth;

    public function __construct()
    {
        $this->web3 = new Web3(new HttpProvider(new HttpRequestManager('https://ropsten.infura.io/v3/bb808692392c4e6daf797b961b13848e')));
        $this->personal = $this->web3->getPersonal();
        $this->eth = $this->web3->getEth();
    }

    public function sendContract()
    {
        $this->personal->personal_newAccount('123456', function ($err, $account) use (&$newAccount) {
            if ($err !== null) {
                echo 'Error: ' . $err->getMessage();
                return;
            }
            $newAccount = $account;
            echo 'New account: ' . $account . PHP_EOL;
        });;

    }

}
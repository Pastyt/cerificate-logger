<?php

namespace App;

use Cloutier\PhpIpfsApi\IPFS;

class IPFSApi
{
    private IPFS $ipfs;

    public function __construct()
    {
        $this->ipfs = new IPFS('localhost', '8080', '5001');
    }

    public function getCertFromIpfs(string $hash)
    {
        $file = $this->ipfs->cat($hash);
        return $file;
    }

    public function addCertToIPFS(array $cert) : string
    {
        $hash = $this->ipfs->add($cert);
        return $hash;
    }
}
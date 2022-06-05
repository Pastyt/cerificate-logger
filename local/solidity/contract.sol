//"SPDX-License-Identifier: UNLICENSED"

pragma solidity  ^0.8.5;

contract CertLogger {

    uint private counter = 0;

    event AddCertificate(uint id, bytes32 indexed cert_id,bytes32 cert_hash,bytes32 issuer_hash, uint  expiry);

    event RevokedCertificate(uint indexed id);

    function addAttribute(bytes32 cert_id,bytes32 cert_hash,bytes32 issuer_hash, uint expiry) public returns (uint) {

        uint id = counter++;

        emit AddCertificate(id, cert_id,cert_hash,issuer_hash, expiry);

        return id;

    }

    function revokeCertificate(uint id) public returns (uint) {
        emit RevokedCertificate(id);
        return id;
    }
}
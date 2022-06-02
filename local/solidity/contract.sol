//"SPDX-License-Identifier: UNLICENSED"

pragma solidity  ^0.8.5;

contract CertLogger {

    uint private counter = 0;

    event AddedCertificate(uint indexed id, bytes32 indexed cert_id, uint  expiry);
    event RevokedCertificate(uint indexed id);

    function addAttribute(bytes32 cert_id, uint expiry) public returns (uint) {

        uint id = counter++;

        emit AddedCertificate(id, cert_id, expiry);

        return id;

    }

    function revokeSignature(uint id) public returns (uint) {
        emit RevokedCertificate(id);
        return id;
    }
}
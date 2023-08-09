<?php

namespace Sapi;

class AesEncryption
{
    private $key;

    public function __construct($key)
    {
        if (empty($key) || strlen($key) != 16) {
//            throw new Exception("AES key must be a 16-character string");
        }
        $this->key = $key;
    }

    public function encrypt($data)
    {
        $iv = random_bytes(16); // 随机生成初始化向量
        $ciphertext = openssl_encrypt($data, 'aes-128-cbc', $this->key, OPENSSL_RAW_DATA, $iv);
        $encryptedData = $iv . $ciphertext;
        return base64_encode($encryptedData);
    }

    public function decrypt($encryptedData)
    {
        $encryptedData = base64_decode($encryptedData);
        $iv = substr($encryptedData, 0, 16);
        $ciphertext = substr($encryptedData, 16);
        return openssl_decrypt($ciphertext, 'aes-128-cbc', $this->key, OPENSSL_RAW_DATA, $iv);
    }
}
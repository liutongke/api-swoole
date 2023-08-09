<?php

namespace Sapi;

class AesEncryption
{
    private string $key;

    // AES 密钥长度选项
    public static int $KEY_LEN_EASY = 16;
    public static int $KEY_LEN_MEDIUM = 24;
    public static int $KEY_LEN_DIFFICULTY = 32;

    public function __construct(int $keyLen)
    {
        $this->key = $this->generateKey($keyLen);
    }

    /**
     * 生成 AES 密钥
     *
     * @param int $keyLen 密钥长度
     * @return string 生成的密钥
     */
    private function generateKey(int $keyLen): string
    {
        $aesKey = openssl_random_pseudo_bytes($keyLen);
        return bin2hex($aesKey);
    }

    /**
     * 对数据进行 AES 加密
     *
     * @param string $data 待加密的数据
     * @return string 加密后的数据
     * @throws \Exception
     */
    public function encrypt(string $data): string
    {
        $iv = random_bytes(16); // 随机生成初始化向量
        $ciphertext = openssl_encrypt($data, 'aes-128-cbc', $this->key, OPENSSL_RAW_DATA, $iv);
        $encryptedData = $iv . $ciphertext;
        return base64_encode($encryptedData);
    }

    /**
     * 对 AES 加密的数据进行解密
     *
     * @param string $encryptedData 加密的数据
     * @return string 解密后的数据
     */
    public function decrypt(string $encryptedData): string
    {
        $encryptedData = base64_decode($encryptedData);
        $iv = substr($encryptedData, 0, 16);
        $ciphertext = substr($encryptedData, 16);
        return openssl_decrypt($ciphertext, 'aes-128-cbc', $this->key, OPENSSL_RAW_DATA, $iv);
    }
}

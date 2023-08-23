<?php

namespace Sapi;

class Tool
{
    /**
     * 生成随机字符串
     *
     * @param int $length 生成的随机字符串的长度
     * @param null $characters 随机生成字符串的字符集，默认为所有可打印 ASCII 字符
     * @return string 生成的随机字符串
     * @throws \Exception
     */
    public static function generateRandomString(int $length, $characters = null): string
    {
        if (!$characters) {
            // 默认字符集为所有可打印 ASCII 字符
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"#$%&\'()*+,-./:;<=>?@[\]^_`{|}~';
        }

        // 计算字符集长度
        $charactersLength = strlen($characters);

        // 初始化随机字符串
        $randomString = '';

        // 生成随机字节并转换为字符串
        $bytes = random_bytes($length);
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[ord($bytes[$i]) % $charactersLength];
        }

        return $randomString;
    }
}
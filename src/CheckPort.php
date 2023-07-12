<?php

namespace Sapi;
class CheckPort
{
    public static function checkPort()
    {
        $config = DI()->config->get('conf');

        foreach ($config as $conf) {
            if (isset($conf['port']) && isset($conf['host'])) {
                self::check($conf['host'], $conf['port']);
            }
        }

    }

    public static function check($host, $port, $timeout = 5)
    {
        // 尝试打开一个网络连接
        $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);

        // 根据连接结果输出检测信息
        if ($fp) {
            fclose($fp);
            Logger::echoErrCmd("Port {$port} is occupied");
            exit();
        }
    }
}
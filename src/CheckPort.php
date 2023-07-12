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

    public static function check($host, $port)
    {
        // TCP 连接检测
        $tcp_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $tcp_result = socket_bind($tcp_socket, $host, $port);


        // 检测结果
        if ($tcp_result === false) {
            socket_close($tcp_socket);
            Logger::echoErrCmd("Port {$port} is occupied");
            exit();
        }

        // UDP 连接检测
        $udp_socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        $udp_result = socket_bind($udp_socket, $host, $port);

        if ($udp_result === false) {
            socket_close($udp_socket);
            Logger::echoErrCmd("Port {$port} is occupied");
            exit();
        }

        socket_close($tcp_socket);
        socket_close($udp_socket);
    }
}
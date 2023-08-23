<?php
/*
 * User: keke
 * Date: 2021/7/16
 * Time: 11:30
 *——————————————————佛祖保佑 ——————————————————
 *                   _ooOoo_
 *                  o8888888o
 *                  88" . "88
 *                  (| -_- |)
 *                  O\  =  /O
 *               ____/`---'\____
 *             .'  \|     |//  `.
 *            /  \|||  :  |||//  \
 *           /  _||||| -:- |||||-  \
 *           |   | \\  -  /// |   |
 *           | \_|  ''\---/''  |   |
 *           \  .-\__  `-`  ___/-. /
 *         ___`. .'  /--.--\  `. . __
 *      ."" '<  `.___\_<|>_/___.'  >'"".
 *     | | :  ` - `.;`\ _ /`;.`/ - ` : | |
 *     \  \ `-.   \_ __\ /__ _/   .-` /  /
 *======`-.____`-.___\_____/___.-`____.-'======
 *                   `=---='
 *——————————————————代码永无BUG —————————————————
 */

namespace Sapi;

class Events
{

    public function __construct($server)
    {
        $this->server = $server;
    }

    public function onOpen(\Swoole\WebSocket\Server $server, $request)
    {
        DI()->EventsRegister->run(EventsName::$onOpen, $server, $request);;
    }

    public function onClose($server, $fd)
    {
        if (DI()->config->get('conf.debug')) {
            echo "client {$fd} closed\n";
        }
        DI()->EventsRegister->run(EventsName::$onClose, $server, $fd);;
    }

    public function onMessage(\Swoole\WebSocket\Server $server, $frame)
    {
//        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        WsRequest::getInstance()->handlerMsg($server, $frame);
    }

    public function onRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
    {
        HttpRequest::getInstance()->handlerMsg($request, $response, $this->server);
    }

    public function onWorkerStart(\Swoole\Server $server, int $workerId)
    {
//        var_dump(get_included_files());
        DI()->EventsRegister->run(EventsName::$onWorkerStart, $server, $workerId);//https://wiki.swoole.com/#/question/use?id=%e6%98%af%e5%90%a6%e5%8f%af%e4%bb%a5%e5%85%b1%e7%94%a81%e4%b8%aaredis%e6%88%96mysql%e8%bf%9e%e6%8e%a5
        if ($server->taskworker) {
            self::setProcessName("swoole server task:{$workerId}");
        } else {
            self::setProcessName("swoole server worker:{$workerId}");
        }
    }

    public static function setProcessName(string $processName)
    {
        swoole_set_process_name($processName);
    }

    //使用 task 必须为 Server 设置 onTask 和 onFinish 回调，否则 Server->start 会失败
    public function onTask(\Swoole\Server $server, int $task_id, int $src_worker_id, array $data)
    {
        DI()->EventsRegister->run(EventsName::$onTask, $server, $task_id, $src_worker_id, $data);
    }

    public function onFinish(\Swoole\Server $server, int $task_id, int $src_worker_id, array $data)
    {
        DI()->EventsRegister->run(EventsName::$onFinish, $server, $task_id, $src_worker_id, $data);;
    }

    public function onStart(\Swoole\Server $server)
    {
        echo <<<EOL
                 _  _____                    _      
     /\         (_)/ ____|                  | |     
    /  \   _ __  _| (_____      _____   ___ | | ___ 
   / /\ \ | '_ \| |\___ \ \ /\ / / _ \ / _ \| |/ _ \
  / ____ \| |_) | |____) \ V  V / (_) | (_) | |  __/
 /_/    \_\ .__/|_|_____/ \_/\_/ \___/ \___/|_|\___|
          | |                                       
          |_|                                       
EOL. "\n";

        $phpVersion = phpversion();
        $swooleVersion = SWOOLE_VERSION;

        Logger::echoMessage("Swoole: {$swooleVersion}, PHP: {$phpVersion}, Port: {$server->port}", Logger::$success);
        Logger::echoMessage("Swoole Http Server running：http://{$server->host}:{$server->port}", Logger::$success);
        Logger::echoMessage("Swoole websocket Server running：ws://{$server->host}:{$server->port}", Logger::$success);
        $tcp_config = DI()->config->get('conf.tcp');
        if (isset($tcp_config)) {
            Logger::echoMessage("Swoole tcp Server running：{$tcp_config['host']}:{$tcp_config['port']}", Logger::$success);
        }

        $udp_config = DI()->config->get('conf.udp');
        if (isset($udp_config)) {
            Logger::echoMessage("Swoole udp Server running：{$udp_config['host']}:{$udp_config['port']}", Logger::$success);
        }
    }
}
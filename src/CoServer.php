<?php
/*
 * User: keke
 * Date: 2021/7/16
 * Time: 11:29
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

class CoServer
{
    use Singleton;

    public \Swoole\WebSocket\Server $server;

    public function __construct()
    {
        $this->initialize();
        $this->mainServer();
        $this->addProcess();
        $a = ["udp", "tcp"];
        foreach ($a as $key => $value) {
            $this->streamServers($key, $value);
        }
    }

    public function initialize()
    {
        DI()->router = router();
        DI()->logger = Logger::getInstance(ROOT_PATH, DI()->config->get('conf.debug'));//初始化日志
        DI()->runTm = Runtime::getInstance(DI()->config->get('conf.debug'));
        DI()->Error = ApiError::getInstance();
        DI()->EventsRegister = EventsRegister::getInstance();
    }

    public function mainServer()
    {
        $ws_config = DI()->config->get('conf.ws');

        $this->initSwooleServer($ws_config['host'], $ws_config['port']);

        if (!empty($ws_config['settings'])) {
            $this->server->set($ws_config['settings']);
        }

        foreach ($ws_config['events'] as $eventsInfo) {
            $this->server->on($eventsInfo['0'], [new $eventsInfo['1']($this->server), $eventsInfo['2']]);
        }
    }

    public function initSwooleServer($host, $prot)
    {
        //https://wiki.swoole.com/#/runtime
        \Swoole\Coroutine::set(['hook_flags' => SWOOLE_HOOK_TCP]);
        $this->server = new \Swoole\WebSocket\Server($host, $prot);
    }

    public function addProcess()
    {
        $process_config = DI()->config->get('conf.process');

        if (!empty($process_config) && is_array($process_config)) {
            foreach ($process_config as $processData) {
                if (isset($processData['0']) && $processData['1']) {
                    $this->server->addProcess(call_user_func([new $processData['0'], $processData['1']], $this->server));
                }
            }
        }
    }

    public function streamServers($k, $v)
    {
        $stream_config = DI()->config->get("conf.{$v}");
        if (!empty($stream_config)) {
            $stream_server = $this->server->addListener($stream_config['host'], $stream_config['port'], $stream_config['sockType']);

            if (!$stream_server) {
                Logger::echoErrCmd("Port {$stream_config['port']} is occupied");
                exit();
            }

            if (!empty($stream_config['settings'])) {
                $stream_server->set($stream_config['settings']);
            }

            foreach ($stream_config['events'] as $eventsInfo) {
                $stream_server->on($eventsInfo['0'], [new $eventsInfo['1']($this->server), $eventsInfo['2']]);
            }
        }
    }

    public function start()
    {
        Events::setProcessName("swoole server Master");
        $this->server->start();
    }

    //获取服务
    public function getServer()
    {
        return $this->server;
    }
}
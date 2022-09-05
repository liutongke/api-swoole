<?php
/*
 * User: keke
 * Date: 2022/9/5
 * Time: 12:19
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


use Sapi\Router\WsRouter;

class WsRequest
{
    use Singleton;

    public function __construct()
    {

    }

    public function handlerWs(\Swoole\WebSocket\Server $server, $frame)
    {
        DI()->runTm->start();

        $ws = new WsResponse($server);
        $ws->setFd($frame->fd);

        register_shutdown_function(function () use ($server, $frame, $ws) {
            $error = error_get_last();

            if (!empty($error)) {
                DI()->logger->error($error['message']);
                $ws->setStatus(HttpCode::$StatusInternalServerError);
                $ws->setCode(HttpCode::$StatusInternalServerError);
                $ws->setMsg($error);
                $ws->output();
            }
        });

        $this->handlerWsData($server, $frame, $ws);

        DI()->logger->echoWsCmd($server, $frame->fd, DI()->runTm->end());

        $ws->output();
    }

    public function handlerWsData(\Swoole\WebSocket\Server $server, $frame, \Sapi\WsResponse $ws)
    {
        $res = json_decode($frame->data, true);
        $list = WsRouter::GetHandlers();
        if (!is_array($res) || !isset($list[strtolower($res['path'])]) || empty($res)) {
            $ws->setCode(HttpCode::$StatusBadRequest);
            $ws->setMsg('data err');
            return;
        }
        $c = $list[strtolower($res['path'])];
        $api = $c['0'];
        $action = $c['1'];
        //先处理必须携带的参数
        $rule = $api->getByRule($res['data'], $action);
        if ($rule['res']) {//验证未通过
            $ws->setId($res['id']);
            $ws->setCode(HttpCode::$StatusBadRequest);
            $ws->setMsg($rule['data']);
            $ws->setPath($res['path']);
            $ws->setData($res['data']);
        } else {
            $data = $api->{$action}($server, $res);
            $ws->setId($res['id']);
            $ws->setCode(HttpCode::$StatusOK);
            $ws->setPath($res['path']);
            $ws->setData($data);
        }
    }
}
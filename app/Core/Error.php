<?php
/*
 * User: keke
 * Date: 2022/9/2
 * Time: 17:58
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

namespace chat\sw\Core;

class Error
{
    use Singleton;

    function __construct()
    {
        set_error_handler([$this, 'errorHandler']);
    }

    public function errorHandler($errno, $errstr, $errfile, $line)
    {
        $errorStr = $this->ErrorLevels($errno);
        DI()->logger->log("{$errorStr}:{$errstr}:{$errfile} {$line}", $errno);
    }

    public function httpBadRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response, $error)
    {
        DI()->logger->error($error['message']);
        $response->status(500);
        $response->end(json_encode(['code' => 500, 'msg' => $error['message']]));
    }
//E_ERROR	1
//E_WARNING	2
//E_PARSE	4
//E_NOTICE	8
//E_CORE_ERROR	16
//E_CORE_WARNING	32
//E_COMPILE_ERROR	64
//E_COMPILE_WARNING	128
//E_USER_ERROR	256
//E_USER_WARNING	512
//E_USER_NOTICE	1024
//E_STRICT	2048
//E_RECOVERABLE_ERROR	4096
//E_DEPRECATED	8192
//E_USER_DEPRECATED	16384
//E_ALL	32767
    public function ErrorLevels($errno): string
    {
        switch ($errno) {
            case 1:
                return 'E_ERROR';
            case 2:
                return 'E_WARNING';
            case 4:
                return 'E_PARSE';
            case 8:
                return 'E_NOTICE';
            case 16:
                return 'E_CORE_ERROR';
            case 32:
                return 'E_CORE_WARNING';
            case 64:
                return 'E_COMPILE_ERROR';
            case 128:
                return 'E_COMPILE_WARNING';
            case 256:
                return 'E_USER_ERROR';
            case 512:
                return 'E_USER_WARNING';
            case 1024:
                return 'E_USER_NOTICE';
            case 2048    :
                return 'E_STRICT';
            case 4096:
                return 'E_RECOVERABLE_ERROR';
            case 8192:
                return 'E_DEPRECATED';
            case 16384:
                return 'E_USER_DEPRECATED';
            case 32767:
                return 'E_ALL';
        }
    }
}
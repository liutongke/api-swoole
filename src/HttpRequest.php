<?php
/*
 * User: keke
 * Date: 2022/9/5
 * Time: 12:08
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

use Sapi\Error\MyError;
use Sapi\Router\HttpRouter;

class HttpRequest
{
    use Singleton;

    public static array $chromeRule = ['/favicon.ico', '/favicon.png'];

    public function __construct()
    {

    }

    public function handlerMsg(\Swoole\Http\Request $request, \Swoole\Http\Response $response, \Swoole\WebSocket\Server $server)
    {

        if (in_array($request->server['path_info'], self::$chromeRule) || in_array($request->server['request_uri'], self::$chromeRule)) {
            $response->end();
            return;
        }

        DI()->runTm->start();

        $rs = new HttpResponse($response);
        try {
            $pathUrl = strtolower($request->server['path_info']);//请求的地址
            $setUrlList = HttpRouter::GetHandlers();
            if (!isset($setUrlList[$pathUrl])) {
                $rs->setStatus(HttpCode::$StatusNotFound);
                $rs->setCode(HttpCode::$StatusNotFound);
                $rs->setData(['url does not exist']);
                $rs->output();
                $rs->end();
                return;
            }

            $routeInfo = $setUrlList[$pathUrl];

            $rs->setStatus(HttpCode::$StatusOK);

            if (is_array($routeInfo) && method_exists($routeInfo['0'], 'getHttpRules')) {//先处理必须携带的参数
                $rule = $routeInfo['0']->getHttpRules($routeInfo['1'], $request);
            }

            if (isset($rule['res']) && $rule['res']) {//验证未通过
                $rs->setCode(HttpCode::$StatusBadRequest);
                $rs->setData([$rule['data']]);
            } else {
                try {
                    $rs->setCode(HttpCode::$StatusOK);
                    $rs->setData(call_user_func_array($routeInfo, [$request, $response, $server]));
                } catch (\Exception $e) {
                    $rs->setCode(HttpCode::$StatusInternalServerError);
                    $rs->setDebug($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
                    DI()->Error->errorHandler($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
                }
            }
            $output = $rs->output();
            DI()->logger->echoHttpCmd($request, $response, $server, DI()->runTm->end(), $output->res);

            $rs->end();
        } catch (\Error $e) {
            DI()->Error->errorHandler($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());

            $rs->setStatus(HttpCode::$StatusInternalServerError);
            $rs->setCode(HttpCode::$StatusInternalServerError);
            $rs->setDebug($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
            $output = $rs->output();
            DI()->logger->echoHttpCmd($request, $response, $server, DI()->runTm->end(), $output->res, HttpCode::$StatusInternalServerError);

            $rs->end();
        }
    }
}
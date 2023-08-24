<?php
/*
 * User: keke
 * Date: 2022/8/26
 * Time: 0:04
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


use Sapi\Format\Format;

class Rule
{
    //        ['engineData' => [
//            'data' => ['name' => 'data', 'require' => true, 'type' => 'string']
//        ]
//        ];
    use Singleton;

    /**
     * 参数处理
     * @param array $data 请求的参数
     * @param string $action 处理请求的方法
     * @param array $rules 处理的规则
     * @return array
     */
    public function getByRule($data, string $action, array $rules): array
    {

        if (!isset($rules[$action])) {
            return ["res" => false, "data" => ""];
        }

        return call_user_func_array([new Format(), 'WsFormat'], [$data, $action, $rules]);
    }

    /**
     * 参数处理
     * @param array $data 请求的参数
     * @param string $action 处理请求的方法
     * @param array $rules 处理的规则
     * @return array
     */
    public function getByHttpRule(\Swoole\Http\Request $request, string $action, array $rules): array
    {

        if (!isset($rules[$action])) {
            return ["res" => false, "data" => ""];
        }

        return call_user_func_array([new Format(), 'HttpFormat'], [$request, $action, $rules]);
    }
}
<?php
/*
 * User: keke
 * Date: 2022/9/2
 * Time: 23:33
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


class Runtime
{
    use Singleton;

    private float $startTime; // 修改成员属性名，提高可读性
    private bool $debug;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
        $this->startTime = 0; // 初始化开始时间
    }

    /**
     * 记录开始时间
     */
    public function start()
    {
        if ($this->debug) {
            $this->startTime = microtime(true);
        }
    }

    /**
     * 计算并返回运行时间
     *
     * @return string 运行时间，单位：毫秒
     */
    public function end(): string
    {
        if ($this->debug) {
            $endTime = microtime(true);
            $runTime = ($endTime - $this->startTime) * 1000;
            return round($runTime, 3) . "ms";
        }
        return "0";
    }
}
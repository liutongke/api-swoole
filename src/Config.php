<?php
/*
 * User: keke
 * Date: 2021/7/13
 * Time: 10:57
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

/**
 * Class Config
 * 处理配置文件的加载和获取。
 */
class Config
{
    /**
     * @var string 配置文件目录的路径。
     */
    private string $path;

    /**
     * @var array 存储已加载的配置数据，以便快速检索。
     */
    private array $confMap = [];

    /**
     * Config 构造函数。
     * @param string $confDirPath 配置文件目录的路径。
     */
    public function __construct(string $confDirPath)
    {
        $this->path = $confDirPath;
    }

    /**
     * 根据提供的键获取配置数据。
     *
     * @param string $key 配置键（包括嵌套键）。
     * @param mixed|null $default 如果键不存在，返回的默认值。
     * @return mixed|null 获取的配置数据或默认值。
     */
    public function get(string $key, $default = null)
    {
        $keyArr = explode('.', $key);
        $fileName = $keyArr['0'];

        if (!isset($this->confMap[$fileName])) {
            $this->loadConfig($fileName);
        }

        $confData = $this->confMap[$fileName];

        if (count($keyArr) == 1) {
            return $confData;
        }

        foreach ($keyArr as $idx) {
            if (isset($confData[$idx])) {
                $data = $confData[$idx];
                break;
            }
        }
        
        return $data ?? $default;
    }

    /**
     * 从文件加载配置数据并将其存储在 confMap 数组中。
     *
     * @param string $fileName 配置文件的名称（不包括扩展名）。
     */
    private function loadConfig($fileName)
    {
        $filePath = $this->path . DIRECTORY_SEPARATOR . $fileName . ".php";
        $this->confMap[$fileName] = include_once($filePath);
    }
}
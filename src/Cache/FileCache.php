<?php

namespace Sapi\Cache;

class FileCache implements Cache
{
    private string $cacheFolderPath;

    /**
     * 构造函数，初始化缓存文件夹路径。
     */
    public function __construct()
    {
        $this->cacheFolderPath = $this->createCacheFileFolder();
    }

    /**
     * 设置缓存。
     *
     * @param string $key 缓存的键名
     * @param string $value 缓存的值
     * @param int $expire 过期时间（秒），默认为 600 秒
     * @return bool 设置成功返回 true，否则返回 false
     */
    public function set(string $key, string $value, $expire = 600): bool
    {
        $filePath = $this->generateFilePath($key);

        // 构造缓存内容数组
        $param = [
            'data' => $value,
            'expire' => $expire,
            'create_tm' => time(),
        ];

        // 将内容序列化并写入文件
        return file_put_contents($filePath, serialize($param)) !== false;
    }

    /**
     * 获取缓存。
     *
     * @param string $key 缓存的键名
     * @return string 获取到的缓存值，如果缓存不存在或已过期则返回空字符串
     */
    public function get(string $key): string
    {
        $filePath = $this->generateFilePath($key);

        // 如果缓存文件不存在，则返回空字符串
        if (!file_exists($filePath)) {
            return '';
        }

        // 从文件中读取内容
        $data = file_get_contents($filePath);
        if ($data === false) {
            return '';
        }

        // 反序列化缓存内容
        $param = unserialize($data);
        // 检查缓存是否过期
        if (time() > ($param['create_tm'] + $param['expire'])) {
            $this->delete($key); // 过期则删除缓存文件
            return '';
        }
        return $param['data'];
    }

    /**
     * 删除缓存。
     *
     * @param string $key 缓存的键名
     * @return void
     */
    public function delete(string $key)
    {
        if ($key === '') {
            return; // 如果 key 为空，则直接返回，不进行操作
        }

        $filePath = $this->generateFilePath($key);

        // 如果文件存在，则删除
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * 生成缓存文件路径。
     *
     * @param string $key 缓存的键名
     * @return string 生成的缓存文件路径
     */
    private function generateFilePath(string $key): string
    {
        $directoryPath = $this->cacheFolderPath . $this->hashToMod($key);

        // 如果目录不存在，则创建
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }

        // 生成缓存文件路径
        return $directoryPath . DIRECTORY_SEPARATOR . sha1($key);
    }

    /**
     * 创建缓存文件夹路径。
     *
     * @return string 缓存文件夹路径
     */
    private function createCacheFileFolder(): string
    {
        return ROOT_PATH . "storage" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR;
    }

    /**
     * 将键哈希转为模数。
     *
     * @param string $key 缓存的键名
     * @param int $numShards 分片数，默认为 100
     * @return string 哈希值转换后的模数
     */
    private function hashToMod(string $key, $numShards = 100): string
    {
        $hashValue = md5($key);
        return strval((hexdec(substr($hashValue, -4)) % $numShards));
    }
}

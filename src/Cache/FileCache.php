<?php

namespace Sapi\Cache;

class FileCache implements Cache
{
    private string $cacheFolderPath;

    public function __construct()
    {
        $this->cacheFolderPath = $this->createCacheFileFolder();
    }

    public function set(string $key, string $value, $expire = 600): bool
    {
        $filePath = $this->generateFilePath($key);

        $param = [
            'data' => $value,
            'expire' => $expire,
            'create_tm' => time(),
        ];

        return file_put_contents($filePath, serialize($param)) !== false;
    }

    public function get(string $key): string
    {
        $filePath = $this->generateFilePath($key);

        if (!file_exists($filePath)) {
            return '';
        }

        $data = file_get_contents($filePath);
        if ($data === false) {
            return '';
        }

        $param = unserialize($data);
        if (time() > ($param['create_tm'] + $param['expire'])) {//超时
            $this->delete($key);
            return '';
        }
        return $param['data'];
    }

    public function delete(string $key)
    {
        if ($key === '') {
            return;
        }

        $filePath = $this->generateFilePath($key);

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    private function generateFilePath(string $key): string
    {
        $directoryPath = $this->cacheFolderPath . $this->hashToMod($key);

        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }

        return $directoryPath . DIRECTORY_SEPARATOR . sha1($key);
    }

    private function createCacheFileFolder(): string
    {
        return ROOT_PATH . "storage" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR;
    }

    private function hashToMod(string $key, $numShards = 100): string
    {
        $hashValue = md5($key);
        return strval((hexdec(substr($hashValue, -4)) % $numShards));
    }
}

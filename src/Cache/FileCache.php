<?php

namespace Sapi\Cache;

class FileCache implements Cache
{
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
        $data = file_get_contents($filePath);
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

        @unlink($filePath);
    }


    private function generateFilePath(string $key): string
    {
        $directoryPath = ROOT_PATH . "storage/cache/" . $this->hashToMod($key);

        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }
        return $directoryPath . '/' . sha1($key);
    }

    private function hashToMod(string $key, $numShards = 100): string
    {
        $hashValue = md5($key); // 使用 md5() 生成哈希值
        // 取模运算
        return strval((hexdec(substr($hashValue, -4)) % $numShards));
    }

}
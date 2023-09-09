<?php

namespace Sapi\Utils;

use Sapi\Cache\FileCache;

class Hp
{
    private const PREFIX_HP = 'hp_';
    private const MAX_HP = 10; // 最大体力值
    private const TTL = 5;    // 体力恢复时间

    private string $hp_key;
    private int $uid;
    private array $hp_list;

    public function __construct(int $uid)
    {
        $this->hp_key = self::PREFIX_HP . $uid;
        $this->uid = $uid;
        $this->initHp();
    }

    /**
     * 初始化体力值
     */
    private function initHp()
    {
        $hpData = $this->getHpData();
        if ($hpData === null) {
            $hpData = [
                'num' => 0,          // 当前体力值
                'last_tm' => time(), // 最后一次更新时间
            ];
            $this->setHpData($hpData);
            return;
        }

        $this->calculateHp($hpData);
    }

    /**
     * 计算体力值
     * @param array $hpData
     */
    private function calculateHp(array $hpData)
    {
        $num = $hpData['num'];
        $last_tm = $hpData['last_tm'];

        if ($num >= self::MAX_HP) {
            return;
        }

        $hp_tm = time() - $last_tm;
        $remainder_tm = $hp_tm % self::TTL;
        $hp = ($hp_tm - $remainder_tm) / self::TTL;

        $hp = min($hp, self::MAX_HP);
        $hpData['num'] = $hp;
        $this->setHpData($hpData);
    }

    /**
     * 获取体力值数据
     * @return array|null
     */
    public function getHpData(): ?array
    {
        // 从存储中获取体力值数据
        // 例如：$data = fetchFromStorage($this->hp_key);
        // 返回存储的数据数组或null

        if (isset($this->hp_list[$this->hp_key])) {
            return $this->hp_list[$this->hp_key];
        }
        $hp_date = (new FileCache())->get($this->hp_key);
        if (empty($hp_date)) {
            return null;
        }
        return json_decode($hp_date, true); // 用实际的存储方法替换null
    }

    /**
     * 设置体力值数据
     * @param array $data
     */
    private function setHpData(array $data)
    {
        $this->hp_list[$this->hp_key] = $data;
        (new FileCache())->set($this->hp_key, json_encode($data));
        // 将体力值数据存储到持久存储中
        // 例如：storeInStorage($this->hp_key, $data);
    }

    public function incr()
    {
        // 增加体力值逻辑
    }

    public function decr()
    {
        // 减少体力值逻辑
    }
}

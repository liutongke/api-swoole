<?php

namespace Sapi\Utils;

class Hp
{
    private static string $prefix_hp = 'hp_';

    private string $hp_key;
    private int $uid;
    private int $max_hp = 10;//最大体力值
    private int $ttl = 5;//体力恢复时间
    private array $hp_list = [];//体力列表

    public function __construct(int $uid)
    {
        $this->hp_key = self::$prefix_hp . $uid;
        $this->uid = $uid;
        $this->initHp();
    }

    private function initHp()
    {
        if (!isset($this->hp_list[$this->uid])) {
            $this->hp_list[$this->uid] = [
                'num' => 0,//当前体力值
                'last_tm' => time(),//最后一次更新时间
            ];
            return;
        }

        $num = $this->hp_list[$this->uid]['num'];

        if ($num >= $this->max_hp) {
            return;
        }
        $last_tm = $this->hp_list[$this->uid]['last_tm'];

        $hp_tm = time() - $last_tm;
        $remainder_tm = $hp_tm / $this->ttl;
        $hp = ($hp_tm - $remainder_tm) / $this->ttl;

        $hp = min($hp, $this->max_hp);
    }

    public function incr()
    {

    }

    public function decr()
    {

    }
}
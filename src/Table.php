<?php

namespace Sapi;

class Table
{
    use Singleton;

    private \Swoole\Table $table;

    public function __construct()
    {
        $this->create();
    }

    private function addColumn(\Swoole\Table $table, array $column)
    {
        if ($column['type'] === \Swoole\Table::TYPE_STRING) {
            $table->column($column['name'], $column['type'], $column['size']);
        } else {
            $table->column($column['name'], $column['type']);
        }
    }

    public function create(): \Swoole\Table
    {
        $table_conf = DI()->config->get('conf.table');

        $table = new \Swoole\Table($table_conf['size'], $table_conf['conflict_proportion']);
        foreach ($table_conf['column'] as $column) {
            $this->addColumn($table, $column);
        }

        $table->create();
        $this->table = $table;

        return $this->table;
    }

    /**
     * 获取表格的最大行数。
     *
     * @return int 最大行数
     */
    public function getSize(): int
    {
        return $this->table->size;
    }

    /**
     * 获取实际占用内存的尺寸，单位为字节。
     *
     * @return int 内存尺寸（字节）
     */
    public function getMemorySize(): int
    {
        return $this->table->memorySize;
    }

    /**
     * 设置行的数据。Table 使用 key-value 的方式来访问数据。
     *
     * @param string $key 数据的 key
     * @param array $value 数据的 value
     * @return bool
     */
    public function set(string $key, array $value): bool
    {
        return $this->table->set($key, $value);
    }

    /**
     * 获取一行数据。
     * @param string $key 数据的 key【必须为字符串类型】
     * @param string|null $field
     * @return array|false
     */
    public function getAll(string $key, string $field = null): array|false
    {
        if (is_null($field)) {
            return $this->table->get($key);
        }
        return $this->table->get($key, $field);
    }

    /**
     * 当指定了 $field 时仅返回该字段的值，而不是整个记录
     * @param string $key 数据的 key【必须为字符串类型】
     * @param string|null $field 当指定了 $field 时仅返回该字段的值，而不是整个记录
     * @return string|false
     */
    public function get(string $key, string $field = null): string|false
    {
        return $this->getAll($key, $field);
    }

    /**
     *检查 table 中是否存在某一个 key。
     * @param string $key 数据的 key【必须为字符串类型】
     * @return bool
     */
    public function exist(string $key): bool
    {
        return $this->table->exist($key);
    }

    /**
     *返回 table 中存在的条目数。
     * @return int
     */
    public function count(): int
    {
        return $this->table->count();
    }

    /**
     * 删除数据。
     * @param string $key $key 对应的数据不存在，将返回 false
     * @return bool
     */
    public function del(string $key): bool
    {
        return $this->table->del($key);
    }

    /**
     * 获取 Swoole\Table 状态。
     * @return array
     */
    public function stats(): array
    {
        // Swoole 版本 >= v4.8.0 可用
        return $this->table->stats();
    }

    /**
     * 原子自增操作。
     * @param string $key 数据的 key【如果 $key 对应的行不存在，默认列的值为 0】
     * @param string $column 指定列名【仅支持浮点型和整型字段】
     * @param int|float $incrby 默认值：1 ,增量 【如果列为 int，$incrby 必须为 int 型，如果列为 float 型，$incrby 必须为 float 类型】
     * @return int|float
     */
    public function incr(string $key, string $column, int|float $incrby = 1): int|float
    {
        return $this->table->incr($key, $column, $incrby);
    }

    /**
     *原子自减操作。
     * @param string $key 数据的 key【如果 $key 对应的行不存在，默认列的值为 0】
     * @param string $column 指定列名【仅支持浮点型和整型字段】
     * @param int|float $decrby 默认值：1 ,增量 【如果列为 int，$decrby 必须为 int 型，如果列为 float 型，$decrby 必须为 float 类型】
     * @return int|float
     */
    public function decr(string $key, string $column, int|float $decrby = 1): int|float
    {
        return $this->table->decr($key, $column, $decrby);
    }
}

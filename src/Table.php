<?php

namespace Sapi;

class Table
{
    use Singleton;

    private static \Swoole\Table $table;

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
        self::$table = $table;

        return self::$table;
    }

    /**
     * 获取表格的最大行数。
     *
     * @return int 最大行数
     */
    public static function getSize(): int
    {
        return self::$table->size;
    }

    /**
     * 获取实际占用内存的尺寸，单位为字节。
     *
     * @return int 内存尺寸（字节）
     */
    public static function getMemorySize(): int
    {
        return self::$table->memorySize;
    }

    /**
     * 设置行的数据。Table 使用 key-value 的方式来访问数据。
     *
     * @param string $key 数据的 key
     * @param array $value 数据的 value
     * @return bool
     */
    public static function set(string $key, array $value): bool
    {
        return self::$table->set($key, $value);
    }

    /**
     * 获取一行数据。
     * @param string $key 数据的 key【必须为字符串类型】
     * @param string|null $field
     * @return array|false
     */
    public static function getAll(string $key): array|false
    {
        return self::$table->get($key);
    }

    /**
     * 当指定了 $field 时仅返回该字段的值，而不是整个记录
     * @param string $key 数据的 key【必须为字符串类型】
     * @param string|null $field 当指定了 $field 时仅返回该字段的值，而不是整个记录
     * @return string|false
     */
    public static function get(string $key, string $field = null): string|false
    {
        return self::$table->get($key, $field);
    }

    /**
     *检查 table 中是否存在某一个 key。
     * @param string $key 数据的 key【必须为字符串类型】
     * @return bool
     */
    public static function exist(string $key): bool
    {
        return self::$table->exist($key);
    }

    /**
     *返回 table 中存在的条目数。
     * @return int
     */
    public static function count(): int
    {
        return self::$table->count();
    }

    /**
     * 删除数据。
     * @param string $key $key 对应的数据不存在，将返回 false
     * @return bool
     */
    public static function del(string $key): bool
    {
        return self::$table->del($key);
    }

    /**
     * 获取 Swoole\Table 状态。
     * @return array
     */
    public static function stats(): array
    {
        return self::$table->stats();
    }

    /**
     * 原子自增操作。
     * @param string $key 数据的 key【如果 $key 对应的行不存在，默认列的值为 0】
     * @param string $column 指定列名【仅支持浮点型和整型字段】
     * @param int|float $incrby 默认值：1 ,增量 【如果列为 int，$incrby 必须为 int 型，如果列为 float 型，$incrby 必须为 float 类型】
     * @return int|float
     */
    public static function incr(string $key, string $column, int|float $incrby = 1): int|float
    {
        return self::$table->incr($key, $column, $incrby);
    }

    /**
     *原子自减操作。
     * @param string $key 数据的 key【如果 $key 对应的行不存在，默认列的值为 0】
     * @param string $column 指定列名【仅支持浮点型和整型字段】
     * @param int|float $decrby 默认值：1 ,增量 【如果列为 int，$decrby 必须为 int 型，如果列为 float 型，$decrby 必须为 float 类型】
     * @return int|float
     */
    public static function decr(string $key, string $column, int|float $decrby = 1): int|float
    {
        return self::$table->decr($key, $column, $decrby);
    }
}

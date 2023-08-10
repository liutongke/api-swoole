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

    public function getSize()
    {

    }

    public function getMemorySize()
    {
    }

    public function set(string $key, array $value): bool
    {
        return $this->table->set($key, $value);
    }

    public function getAll(string $key, string $field = null): array|false
    {
        if (is_null($field)) {
            return $this->table->get($key);
        }
        return $this->table->get($key, $field);
    }

    public function get(string $key, string $field = null): string|false
    {
        return $this->table->get($key, $field);
    }

    public function exist(string $key): bool
    {

    }

    public function count(): int
    {

    }

    public function del(string $key): bool
    {

    }

    public function stats(): array
    {
//        Swoole 版本 >= v4.8.0 可用
        return $this->table->stats();
    }

    public function incr(string $key, string $column, int $incrby = 1): int
    {
    }

    public function decr(string $key, string $column, int $decrby = 1): int
    {

    }
}

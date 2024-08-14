<?php

namespace Sapi;

class Lock
{
    use Singleton;

    private \Swoole\Lock $lock;

    public function __construct()
    {
        $this->lock = new \Swoole\Lock(SWOOLE_MUTEX);
    }

    public function acquire(): bool
    {
        return $this->lock->lock();
    }

    public function release()
    {
        $this->lock->unlock();
    }
}
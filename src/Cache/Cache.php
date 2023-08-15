<?php

namespace Sapi\Cache;

interface Cache
{

    public function set(string $key, string $value, $expire = 600);


    public function get(string $key);


    public function delete(string $key);
}
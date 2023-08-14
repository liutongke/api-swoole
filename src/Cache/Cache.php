<?php

namespace Sapi\Cache;

interface Cache
{

    public function set($key, $value, $expire = 600);


    public function get($key);


    public function delete($key);
}
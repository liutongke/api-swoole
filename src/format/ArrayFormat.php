<?php

namespace Sapi\format;

class ArrayFormat
{
    public function parse($val, $rule): bool
    {
        // TODO: Implement parse() method.
        return is_array($val);
    }
}
<?php

namespace Sapi\Format;

class ArrayFormat
{
    public function parse($val, $rule): bool
    {
        // TODO: Implement parse() method.
        return is_array($val);
    }
}
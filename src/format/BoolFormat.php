<?php

namespace Sapi\format;

class BoolFormat
{
    public function parse($val, $rule): bool
    {
        // TODO: Implement parse() method.
        return is_bool($val);
    }
}
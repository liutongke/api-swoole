<?php

namespace Sapi\Format;

class StringFormat
{
    public function parse($val, $rule): bool
    {
        // TODO: Implement parse() method.
        return is_string($val);
    }
}
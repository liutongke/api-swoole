<?php

namespace Sapi\format;

class IntFormat implements Formatter
{
    public function parse($val, $rule): bool
    {
        // TODO: Implement parse() method.
        return is_numeric($val);
    }
}
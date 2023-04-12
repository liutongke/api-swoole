<?php

namespace Sapi\format;

class FloatFormat
{
    public function parse($val): bool
    {
        // TODO: Implement parse() method.
        return is_float($val);
    }
}
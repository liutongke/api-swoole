<?php

namespace Sapi\format;

class ArrayFormat
{
    public function parse($val): bool
    {
        // TODO: Implement parse() method.
        return is_array($val);
    }
}
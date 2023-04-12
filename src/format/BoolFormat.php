<?php

namespace Sapi\format;

class BoolFormat
{
    public function parse($val): bool
    {
        // TODO: Implement parse() method.
        return is_bool($val);
    }
}
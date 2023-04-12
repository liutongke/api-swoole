<?php

namespace Sapi\format;

class StringFormat
{
    public function parse($val): bool
    {
        // TODO: Implement parse() method.
        return is_string($val);
    }
}
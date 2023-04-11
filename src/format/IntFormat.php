<?php

namespace Sapi\format;

class IntFormat implements Formatter
{
    public function parse($val): bool
    {
        // TODO: Implement parse() method.
        return is_numeric($val);
    }
}
<?php

namespace Sapi\format;

class FileFormat
{
    public function parse($val): bool
    {
        // TODO: Implement parse() method.
        return is_array($val);
    }
}
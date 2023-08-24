<?php

namespace Sapi\Format;

class BoolFormat
{
    public function parse($val, $rule): bool
    {
        if ($val === "true" || $val === "false") {
            return true;
        } else {
            return false;
        }
    }
}
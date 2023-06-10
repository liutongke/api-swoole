<?php

namespace Sapi\format;

interface Formatter
{
    public function parse($value, array $rule): bool;
}
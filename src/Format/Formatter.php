<?php

namespace Sapi\Format;

interface Formatter
{
    public function parse($value, array $rule): bool;
}
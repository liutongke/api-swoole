<?php

namespace Sapi\format;

interface Formatter
{
    public function parse($value): bool;
}
<?php

namespace Sapi\format;

class FloatFormat
{
    public function parse($val, $rule): bool
    {
        // 尝试将字符串转换为浮点数
        $floatValue = (float)$val;

        // 检查转换后的值是否与原始字符串相等，并且类型为浮点数
        if ((string)$floatValue === $val && is_float($floatValue)) {
            return true;
        } else {
            return false;
        }
    }
}
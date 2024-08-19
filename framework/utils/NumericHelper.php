<?php

namespace framework\utils;

class NumericHelper
{
    public static function toFloat($str, bool $isNeedReplace = false) : float
    {
        $str = $isNeedReplace ? str_replace(",", ".", $str) : $str;
        return floatval($str) == (float)$str ? (float)$str : 0;
    }
    public static function toFloatOrNull($str, bool $isNeedReplace = false) : ?float
    {
        $str = $isNeedReplace ? str_replace(",", ".", $str) : $str;
        return $str != null && floatval($str) == (float)$str ? (float)$str : null;
    }
    public static function toInt($str) : int
    {
        return intval($str) == (int)$str ? (int)$str : 0;
    }
    public static function toIntOrNull($str) : ?int
    {
        return $str != null && intval($str) == (int)$str ? (int)$str : null;
    }
}
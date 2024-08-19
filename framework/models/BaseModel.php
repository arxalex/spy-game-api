<?php

namespace framework\models;

use framework\utils\NumericHelper;

class BaseModel
{
    public ?int $id;

    public function __construct(
        ?int $id = null
    )
    {
        $this->id = $id;
    }

    public function stringConvert($value, bool $isNull = false)
    {
        if ($isNull && ($value == null || $value == "")) {
            return null;
        }
        if (is_numeric($value)) {
            return NumericHelper::toFloat($value);
        } elseif (is_string($value)) {
            if (is_object(json_decode($value))) {
                return json_decode($value);
            } elseif (is_array(json_decode($value))) {
                return json_decode($value);
            }
        } elseif (is_array($value)) {
            return $value;
        } elseif (is_object($value)) {
            return $value;
        }
    }

    public static function isJson(?string $string)
    {
        return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
    }

    public static function arrayToObject(array $DTO)
    {
        $object = new self();
        foreach ($DTO as $key => $value) {
            if (key_exists($key, self::$keys)) {
                $object->$key = $value;
            }
        }
        return $object;
    }

    protected static array $keys = ['id'];
}

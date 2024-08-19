<?php

namespace framework\utils;

class SqlHelper
{
    static function unicodeString($str, $encoding = null)
    {
        if (is_null($encoding)) $encoding = ini_get('mbstring.internal_encoding');
        return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/u', function ($match) use ($encoding) {
            return mb_convert_encoding(pack('H*', $match[1]), $encoding, 'UTF-16BE');
        }, $str);
    }
    static function mysql_escape_mimic($inp)
    {
        if (is_array($inp))
            return array_map(__METHOD__, $inp);

        if (!empty($inp) && is_string($inp)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
        }

        return $inp;
    }
    /**
     * Return query string that goes before and after "VALUES "
     */
    public static function insertObjects(array $objects)
    {
        $queryBefore = "(";
        $queryAfter = "";
        $isNeededBefore = true;

        foreach ($objects as $obj) {
            $queryAfter .= "(";

            if ($isNeededBefore) {
                foreach ($obj as $key => $value) {
                    $queryBefore .= "$key, ";
                }
                $queryBefore = substr($queryBefore, 0, -2) . ")";
                $isNeededBefore = false;
            }

            foreach ($obj as $value) {

                $v = "DEFAULT";
                if (is_array($value)) {
                    $v = "'" . json_encode($value) . "'";
                } elseif (is_object($value)) {
                    $v = "'" . json_encode($value) . "'";
                } elseif ($value == null) {
                    $v = "DEFAULT";
                } else {
                    $v = "'" . self::mysql_escape_mimic($value) . "'";
                }
                $queryAfter .= "$v, ";
            }

            $queryAfter = substr($queryAfter, 0, -2) . "), ";
        }
        return $queryBefore . " VALUES " . substr($queryAfter, 0, -2);
    }

    /**
     * Return query string that goes after "SET "
     */
    public static function updateObject($object)
    {
        $query = "";

        foreach ($object as $key => $value) {
            $v = "DEFAULT";
            if (is_array($value)) {
                $v = "'" . json_encode($value) . "'";
            } elseif (is_object($value)) {
                $v = "'" . json_encode($value) . "'";
            } elseif ($value == null) {
                $v = "DEFAULT";
            } else {
                $v = "'" . self::mysql_escape_mimic($value) . "'";
            }
            $query .= "`$key` = $v, ";
        }

        $query = substr($query, 0, -2);
        return $query;
    }

    /**
     * Return query string that implements ($value[0], ...) for "in"
     */
    public static function arrayInNumeric(array $values)
    {
        $result = "(";
        foreach ($values as $value) {
            $value = self::mysql_escape_mimic($value);
            $result .= "$value, ";
        }
        $result = substr($result, 0, -2) . ")";
        return $result;
    }

    /**
     * Return query string that implements ($value[0], ...) for "in"
     */
    public static function arrayInString(array $values)
    {
        $result = "(";
        foreach ($values as $value) {
            $value = self::mysql_escape_mimic($value);
            $result .= "'$value', ";
        }
        $result = substr($result, 0, -2) . ")";
        return $result;
    }

    /**
     * Return query string that implements ($value[0], ...) for "in"
     */
    public static function arrayLikeString(string $key, array $values)
    {
        $result = "(";
        foreach ($values as $value) {
            $value = self::mysql_escape_mimic($value);
            $result .= "`$key` LIKE '%$value%' OR ";
        }
        $result = substr($result, 0, -4) . ")";
        return $result;
    }

    /**
     * Return query string that goes after "WHERE "
     */
    public static function whereCreate(array $where)
    {
        $query = "";
        foreach ($where as $key => $value) {
            if (is_array($value) && count($value) == 1) {
                if (is_numeric($value[0])) {
                    $query .= "`$key` = ";
                    $query .= "'" . $value[0] . "'";
                    $query .= " AND ";
                } elseif (is_string($value[0]) && substr($key, -5) == "_like") {
                    $key = substr($key, 0, -5);
                    $query .= self::arrayLikeString($key, $value);
                    $query .= " AND ";
                } elseif (is_string($value[0]) && substr($key, -5) != "_like") {
                    $query .= "`$key` = ";
                    $query .= "'" . $value[0] . "'";
                    $query .= " AND ";
                } elseif ($value[0] == NULL) {
                    $query .= "`$key` is NULL";
                    $query .= " AND ";
                }
            }
            elseif (array_key_exists(0, $value)) {
                if (is_numeric($value[0])) {
                    $query .= "`$key` in ";
                    $query .= self::arrayInNumeric($value);
                    $query .= " AND ";
                } elseif (is_string($value[0]) && substr($key, -5) == "_like") {
                    $key = substr($key, 0, -5);
                    $query .= self::arrayLikeString($key, $value);
                    $query .= " AND ";
                } elseif (is_string($value[0]) && substr($key, -5) != "_like") {
                    $query .= "`$key` in ";
                    $query .= self::arrayInString($value);
                    $query .= " AND ";
                } elseif ($value[0] == NULL && count($value) == 1) {
                    $query .= "`$key` is NULL";
                    $query .= " AND ";
                }
            }
        }
        $query = substr($query, 0, -5);
        return $query;
    }
}

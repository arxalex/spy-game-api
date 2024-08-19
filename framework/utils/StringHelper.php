<?php

namespace framework\utils;

class StringHelper
{
    private const ALL_CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const CAPS_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public static function generateRsndomString(int $length = 6, bool $onlyCaps = false): string
    {
        $characters = $onlyCaps ? self::CAPS_CHARS : self::ALL_CHARS;
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    public static function quotesReplacer(string $str): string
    {
        return str_replace(
            array('®', '™', '©', '‘', '’', '‚', '‛', '❛', '❜', '＇', '«', '»', '‹', '›', '“', '”', '„', '‟', '❝', '❞', '〝', '〞', '〟', '＂'),
            array('', '', '', '\'', '\'', '\'', '\'', '\'', '\'', '\'', '"', '"', '"', '"', '"', '"', '"', '"', '"', '"', '"', '"', '"', '"'),
            $str
        );
    }
    public static function getLabelsInsideQoutes(string $str): array
    {
        preg_match('/".*?"/', $str, $matches);
        return $matches;
    }
    public static function nameToKeywords(string $str): array
    {
        $nameStr = StringHelper::quotesReplacer($str);
        $nameStrWoQ = str_replace('"', '', $nameStr);
        $preResult = explode(' ', $nameStrWoQ);
        $result = [];
        foreach ($preResult as $value) {
            if ($value != null && $value != "") {
                $result[] = $value;
            }
        }
        foreach($result as $value){
            $result[] = substr($value, 0, -1);
        }
        return $result;
    }
    public static function stringCleaner($str): string
    {
        return preg_replace('~[^\p{Cyrillic}a-z0-9_\s-]+~ui', '', $str);
    }
    public static function rateItemsByKeywords(string $label, array $items): array
    {
        $labelArr = self::nameToKeywords(self::stringCleaner($label));
        $rates = [];
        foreach ($items as $key => $item) {
            $itemStrArr = self::nameToKeywords(self::stringCleaner($item));
            $tempRate = 0;
            $tempRate += $item == $label ? count($labelArr) * 8 : 0;
            $tempRate += self::stringContains($item, $label) ? count($labelArr) * 4 : 0;
            $tempRate += self::stringContains($label, $item) ? count($itemStrArr) * 4 : 0;
            $i = 0;
            $maxI = count($labelArr);
            foreach ($itemStrArr as $itemStr) {
                if ($itemStr == $labelArr[$i]) {
                    $tempRate += 2;
                    $i++;
                    if ($i == $maxI) {
                        break;
                    }
                } else {
                    continue;
                }
            }
            $i = 0;
            $maxI = count($itemStrArr);
            foreach ($labelArr as $labelStr) {
                if ($labelStr == $itemStrArr[$i]) {
                    $tempRate += 2;
                    $i++;
                    if ($i == $maxI) {
                        break;
                    }
                } else {
                    continue;
                }
            }
            foreach ($itemStrArr as $itemStr) {
                foreach ($labelArr as $labelStr) {
                    $tempRate += $labelStr == $itemStr ? 1 : 0;
                }
            }
            $rates[$key] = $tempRate;
        }
        return $rates;
    }
    public static function stringContains($haystack, $needle)
    {
        return strpos($haystack, $needle) === false ? false : true;
    }
    public static function latinValidate(string $str) : bool
    {
        $strArr = str_split($str);
        $allowed = '0123456789abcdefghijklmnopqrstuvwxyz_.';
        foreach($strArr as $character){
            if(!self::stringContains($allowed, $character)){
                return false;
                break;
            }
        }
        return true;
    }
}

<?php

namespace models;

use framework\models\BaseModel;

class Word extends BaseModel
{
    public ?int $setid;
    public ?string $word;

    public function __construct(
        ?int    $id = null,
        ?int    $setid = null,
        ?string $word = null
    )
    {
        parent::__construct($id);
        $this->setid = $setid;
        $this->word = $word;
    }

    public static function arrayToObjects(?array $wordsDTO): array
    {
        $words = [];
        if(is_array($wordsDTO)) {
            foreach ($wordsDTO as $key => $value) {
                $words[$key] = self::arrayToObject($value);
            }
        }

        return $words;
    }

    public static function arrayToObject(?array $DTO): ?self
    {
        if($DTO == null){
            return null;
        }
        $object = new self();
        foreach ($DTO as $key => $value) {
            if (in_array($key, self::$keys)) {
                $object->$key = $value;
            }
        }
        return $object;
    }

    protected static array $keys = ['id', 'setid', 'word'];
}
<?php

namespace models;

use framework\models\BaseModel;

class User extends BaseModel
{
    public ?string $pass;
    public ?string $name;

    public function __construct(
        ?int    $id = null,
        ?string $pass = null,
        ?string $name = null
    )
    {
        parent::__construct($id);
        $this->pass = $pass;
        $this->name = $name;
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

    protected static array $keys = ['id', 'pass', 'name'];
}
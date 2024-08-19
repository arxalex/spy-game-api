<?php

namespace models;

use framework\models\BaseModel;

class Game extends BaseModel
{
    public ?string $pass;
    public ?int $setid;
    public ?bool $started;
    public ?int $wordid;
    public ?int $stoptime;
    public ?bool $infinitemode;
    public ?int $duration;

    public function __construct(
        ?int    $id = null,
        ?string $pass = null,
        ?int    $setid = null,
        ?bool   $started = null,
        ?int    $wordid = null,
        ?int    $stoptime = null,
        ?bool   $infinitemode = null,
        ?int    $duration = null
    )
    {
        parent::__construct($id);
        $this->pass = $pass;
        $this->setid = $setid;
        $this->started = $started;
        $this->wordid = $wordid;
        $this->stoptime = $stoptime;
        $this->infinitemode = $infinitemode;
        $this->duration = $duration;
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

    protected static array $keys = ['id', 'pass', 'setid', 'started', 'wordid', 'stoptime', 'infinitemode', 'duration'];
}
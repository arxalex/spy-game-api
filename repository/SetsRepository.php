<?php

namespace repository;

use framework\repositories\BaseRepository;

class SetsRepository extends BaseRepository
{
    public function __construct()
    {
        $this->className = "models\\Set";
        $this->tableName = "sg_sets";
    }
}

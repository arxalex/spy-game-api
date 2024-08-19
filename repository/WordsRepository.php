<?php

namespace repository;

use framework\repositories\BaseRepository;

class WordsRepository extends BaseRepository
{
    public function __construct()
    {
        $this->className = "models\\Word";
        $this->tableName = "sg_words";
    }
}

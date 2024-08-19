<?php

namespace repository;

use framework\repositories\BaseRepository;

class GamesRepository extends BaseRepository
{
    public function __construct()
    {
        $this->className = "models\\Game";
        $this->tableName = "sg_games";
    }
}

<?php

namespace repository;

use framework\repositories\BaseRepository;

class GameUsersRepository extends BaseRepository
{
    public function __construct()
    {
        $this->className = "models\\GameUser";
        $this->tableName = "sg_users_games";
    }
}

<?php

namespace repository;

use framework\repositories\BaseRepository;

class UsersRepository extends BaseRepository
{
    public function __construct()
    {
        $this->className = "models\\User";
        $this->tableName = "sg_users";
    }
}

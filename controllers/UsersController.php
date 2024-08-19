<?php

namespace controllers;

use framework\endpoints\BaseEndpoint;
use models\User;
use services\UsersService;

class UsersController extends BaseEndpoint
{
    private UsersService $_usersService;

    public function __construct()
    {
        parent::__construct();
        $this->_usersService = new UsersService();
    }

    public function defaultParams()
    {
        return [
            'method' => "",
            'user' => null
        ];
    }

    public function build()
    {
        $user = User::arrayToObject($this->getParam('user'));

        if ($this->getParam('method') == "generateUser") {
            return $this->_usersService->generateUser($user->name);
        } elseif ($this->getParam('method') == "isUserExists") {
            return $this->_usersService->isUserExists($user);
        } elseif ($this->getParam('method') == "getUserInfo") {
            return $this->_usersService->getUserInfo($user);
        } elseif ($this->getParam('method') == "changeName") {
            return $this->_usersService->changeName($user);
        }

    }
}

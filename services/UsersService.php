<?php

namespace services;

use framework\utils\StringHelper;
use models\User;
use repository\UsersRepository;

class UsersService
{
    private UsersRepository $_usersRepository;
    public function __construct()
    {
        $this->_usersRepository = new UsersRepository();
    }

    public function generateUser(string $name): User
    {
        $user = new User(null, StringHelper::generateRsndomString(6, true), $name);
        $this->_usersRepository->insertItemToDB($user);
        return $this->_usersRepository->getLastInsertedItem();
    }

    public function isUserExists(User $user): bool
    {
        $userFromDb = $this->_usersRepository->getItemFromDB($user->id);
        return $userFromDb != null && $userFromDb->pass == $user->pass;
    }

    public function getUser(int $id) : User {
        return $this->_usersRepository->getItemFromDB($id);
    }

    public function getUsers(array $ids) : array {
        $users = $this->_usersRepository->getItemsFromDB(['id' => $ids]);
        $result = [];
        foreach ($users as $user){
            $user->pass = '';
            $result[] = $user;
        }

        return $result;
    }


    public function getUserInfo(User $user) : ?User {
        if($this->isUserExists($user)) {
            return $this->_usersRepository->getItemFromDB($user->id);
        }

        return null;
    }

    public function changeName(User $user): bool
    {
        if($this->isUserExists($user)) {
            $userFromDb = $this->_usersRepository->getItemFromDB($user->id);
            $userFromDb->name = $user->name;
            $this->_usersRepository->updateItemInDB($userFromDb);
            return true;
        }

        return false;
    }
}

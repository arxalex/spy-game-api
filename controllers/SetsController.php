<?php

namespace controllers;

use framework\endpoints\BaseEndpoint;
use models\Set;
use models\User;
use models\Word;
use services\SetsService;

class SetsController extends BaseEndpoint
{
    private SetsService $_setsService;

    public function __construct()
    {
        parent::__construct();
        $this->_setsService = new SetsService();
    }

    public function defaultParams()
    {
        return [
            'method' => "",
            'set' => [], // Set
            'user' => [], // User
            'words' => [] // array of Word
        ];
    }

    public function build()
    {
        $set = Set::arrayToObject($this->getParam('set'));
        $words = Word::arrayToObjects($this->getParam('words'));
        $user = User::arrayToObject($this->getParam('user'));
        $method = $this->getParam('method');

        if ($method == "getSet") {
            return $this->_setsService->getSet($set->id);
        } elseif ($method == "createSet") {
            return $this->_setsService->createSet($set->name, $user);
        } elseif ($method == "updateSet") {
            return $this->_setsService->updateSet($set, $words, $user);
        } elseif ($method == "deleteSet") {
            return $this->_setsService->deleteSet($set->id, $user);
        } elseif ($method == "getList") {
            return $this->_setsService->getList($user);
        } elseif ($method == "getListPublic") {
            return $this->_setsService->getListPublic();
        }

        return null;

    }
}

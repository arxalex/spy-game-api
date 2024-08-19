<?php

namespace controllers;

use framework\endpoints\BaseEndpoint;
use models\Game;
use models\User;
use services\GamesService;

class GamesController extends BaseEndpoint
{
    private GamesService $_gamesService;

    public function __construct()
    {
        parent::__construct();
        $this->_gamesService = new GamesService();
    }

    public function defaultParams()
    {
        return [
            'method' => "",
            'user' => null,
            'game' => null,
            'userId' => null
        ];
    }

    public function build()
    {
        $game = Game::arrayToObject($this->getParam('game'));
        $user = User::arrayToObject($this->getParam('user'));
        $userId = $this->getParam('userId');
        $method = $this->getParam('method');

        if ($method == "getGameInfo") {
            return $this->_gamesService->getGameInfo($game, $user);
        } elseif ($method == "generateGame") {
            return $this->_gamesService->generateGame($user);
        } elseif ($method == "joinGame") {
            return $this->_gamesService->joinGame($game, $user);
        } elseif ($method == "quitFromGame") {
            return $this->_gamesService->quitFromGame($game, $user);
        } elseif ($method == "startGame") {
            return $this->_gamesService->startGame($game, $user);
        } elseif ($method == "stopGame") {
            return $this->_gamesService->stopGame($game, $user);
        } elseif ($method == "kickUser") {
            return $this->_gamesService->kickUser($game, $user, $userId);
        } elseif ($method == "changeMode") {
            return $this->_gamesService->changeMode($game, $user);
        } elseif ($method == 'isAdmin') {
            return $this->_gamesService->isAdmin($game, $user);
        } elseif ($method == 'getUsers') {
            return $this->_gamesService->getUsers($game, $user);
        }

        return null;
    }
}

<?php

namespace services;

use framework\utils\ListHelper;
use framework\utils\StringHelper;
use models\Game;
use models\GameUser;
use models\User;
use repository\GamesRepository;
use repository\GameUsersRepository;

class GamesService
{
    private GamesRepository $_gamesRepository;
    private GameUsersRepository $_gameUsersRepository;
    private UsersService $_usersService;
    private SetsService $_setsService;

    public function __construct()
    {
        $this->_gamesRepository = new GamesRepository();
        $this->_gameUsersRepository = new GameUsersRepository();
        $this->_usersService = new UsersService();
        $this->_setsService = new SetsService();
    }

    public function generateGame(User $user): ?Game
    {
        if ($this->_usersService->isUserExists($user)) {
            $game = new Game(null, StringHelper::generateRsndomString(6, true), null, null, null, null, null, null);
            $this->_gamesRepository->insertItemToDB($game);
            $gameFromDB = $this->_gamesRepository->getLastInsertedItem();
            $gameUser = new GameUser(null, $user->id, $gameFromDB->id, null, true);
            $this->_gameUsersRepository->insertItemToDB($gameUser);
            return $gameFromDB;
        }
        return null;
    }

    public function getGameInfo(Game $game, User $user): ?Game
    {
        if ($this->isUserInGame($game->id, $user)) {
            $gameFromDb = $this->_gamesRepository->getItemFromDB($game->id);
            if ($this->isSpy($game, $user)) {
                $gameFromDb->wordid = null;
            }
            if (!$gameFromDb->infinitemode && $gameFromDb->stoptime <= time()){
                $this->stopGameSuper($game);
                $gameFromDb = $this->_gamesRepository->getItemFromDB($game->id);
            }

            return $gameFromDb;
        }

        return null;
    }

    public function joinGame(Game $game, User $user): bool
    {
        if (!$this->isUserInGame($game->id, $user)) {
            if (!$this->isGameStarted($game)) {
                $gameUser = new GameUser(null, $user->id, $game->id, null, null);
                $this->_gameUsersRepository->insertItemToDB($gameUser);
                return true;
            }
            return false;
        } else {
            return true;
        }
    }

    public function quitFromGame(Game $game, User $user): bool
    {
        if ($this->isUserInGame($game->id, $user) && !$this->isGameStarted($game)) {
            $gameUser = $this->getGameUser($game->id, $user->id);
            $this->_gameUsersRepository->deleteItem($gameUser);
            return true;
        }
        return false;
    }

    public function startGame(Game $game, User $user): bool
    {
        if (!$this->isGameStarted($game) && $this->isUserGameOwner($game->id, $user)) {
            $gameFromDb = $this->_gamesRepository->getItemFromDB($game->id);
            $gameFromDb->started = true;

            $setAndWords = $this->_setsService->getSet($gameFromDb->setid);
            $gameFromDb->wordid = $this->_setsService->getRandomWord($setAndWords)->id;

            if(!$gameFromDb->infinitemode){
                $gameFromDb->stoptime = time() + $gameFromDb->duration;
            }

            $this->_gamesRepository->updateItemInDB($gameFromDb);

            $gameUsersFromDB = $this->_gameUsersRepository->getItemsFromDB(['gameid' => [$game->id], 'owner' => [0]]);
            $nextSpy = rand(0, count($gameUsersFromDB) - 1);
            foreach ($gameUsersFromDB as $key => $gameUserFromDB){
                if($gameUserFromDB->spy) {
                    $gameUserFromDB->spy = false;
                    $this->_gameUsersRepository->updateItemInDB($gameUserFromDB);
                }
                if($key == $nextSpy){
                    $gameUserFromDB->spy = true;
                    $this->_gameUsersRepository->updateItemInDB($gameUserFromDB);
                }
            }
            return true;
        }
        return false;
    }

    public function stopGame(Game $game, User $user): bool
    {
        if ($this->isGameStarted($game) && $this->isUserGameOwner($game->id, $user)) {
            $game = $this->_gamesRepository->getItemFromDB($game->id);
            $game->started = false;
            $game->stoptime = null;
            $this->_gamesRepository->updateItemInDB($game);
            return true;
        }
        return false;
    }

    public function kickUser(Game $game, User $owner, int $userId): bool
    {
        if ($this->isUserGameOwner($game->id, $owner)) {
            $user = $this->_usersService->getUser($userId);
            return $this->quitFromGame($game, $user);
        }
        return false;
    }

    public function changeMode(Game $game, User $user): bool
    {
        if (!$this->isGameStarted($game) && $this->isUserGameOwner($game->id, $user)) {
            $gameFromDb = $this->_gamesRepository->getItemFromDB($game->id);
            $gameFromDb->setid = $game->setid;
            $gameFromDb->duration = $game->duration;
            $gameFromDb->infinitemode = $game->infinitemode;
            $this->_gamesRepository->updateItemInDB($gameFromDb);
            return true;
        }

        return false;
    }

    public function getUsers(Game $game, User $user): array
    {
        if ($this->isUserGameOwner($game->id, $user)) {
            $gameUsersFromDB = $this->_gameUsersRepository->getItemsFromDB(['gameid' => [$game->id], 'owner' => [0]]);
            return $this->_usersService->getUsers(ListHelper::getColumn($gameUsersFromDB, 'userid'));
        }

        return [];
    }

    public function isAdmin(Game $game, User $user): bool
    {
        return $this->isUserGameOwner($game->id, $user);
    }

    private function isGameExists(Game $game): bool
    {
        $gameFromDb = $this->_gamesRepository->getItemFromDB($game->id);
        return $gameFromDb != null && $gameFromDb->pass == $game->pass;
    }

    private function isGameStarted(Game $game): bool
    {
        $gameFromDb = $this->_gamesRepository->getItemFromDB($game->id);
        return $gameFromDb != null && $gameFromDb->pass == $game->pass && $gameFromDb->started == true;
    }

    private function isUserInGame(int $gameId, User $user): bool
    {
        if ($this->_usersService->isUserExists($user)) {
            $gameUser = $this->getGameUser($gameId, $user->id);
            return $gameUser != null;
        }
        return false;
    }

    private function isUserGameOwner(int $gameId, User $user): bool
    {
        if ($this->_usersService->isUserExists($user)) {
            $gameUser = $this->getGameUser($gameId, $user->id);
            return $gameUser != null && $gameUser->owner == true;
        }
        return false;
    }

    private function getGameUser(int $gameId, int $userId): ?GameUser
    {
        $gameUsers = $this->_gameUsersRepository->getItemsFromDB(['userid' => [$userId], 'gameid' => [$gameId]]);
        return count($gameUsers) == 1 ? $gameUsers[0] : null;
    }

    private function isSpy(Game $game, User $user): bool
    {
        if ($this->isUserInGame($game->id, $user) && $this->isGameStarted($game)) {
            $gameUser = $this->getGameUser($game->id, $user->id);
            return $gameUser->spy;
        }
        return false;
    }

    private function stopGameSuper(Game $game): bool
    {
        if ($this->isGameStarted($game)) {
            $game = $this->_gamesRepository->getItemFromDB($game->id);
            $game->started = false;
            $game->stoptime = null;
            $this->_gamesRepository->updateItemInDB($game);
            return true;
        }
        return false;
    }
}

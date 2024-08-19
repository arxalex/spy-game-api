<?php

namespace services;

use framework\utils\ListHelper;
use models\Set;
use models\User;
use models\Word;
use repository\GamesRepository;
use repository\SetsRepository;
use repository\WordsRepository;
use stdClass;

class SetsService
{
    private UsersService $_usersService;
    private SetsRepository $_setsRepository;
    private WordsRepository $_wordsRepository;
    private GamesRepository $_gamesRepository;

    public function __construct()
    {
        $this->_setsRepository = new SetsRepository();
        $this->_wordsRepository = new WordsRepository();
        $this->_usersService = new UsersService();
        $this->_gamesRepository = new GamesRepository();
    }

    public function getSet(int $setId): object
    {
        $set = $this->_setsRepository->getItemFromDB($setId);
        $words = $this->_wordsRepository->getItemsFromDB(['setid' => [$setId]]);
        $result = new stdClass();

        $result->set = $set;
        $result->words = $words;

        return $result;
    }

    public function createSet(string $name, User $user): ?Set
    {
        if ($this->_usersService->isUserExists($user)) {
            $set = new Set(null, $name, $user->id);
            $this->_setsRepository->insertItemToDB($set);
            return $this->_setsRepository->getLastInsertedItem();
        }

        return null;
    }

    public function updateSet(Set $set, array $words, User $user): bool
    {
        if ($this->isUserOwner($set, $user)) {
            $this->_setsRepository->updateItemInDB($set);
            $wordsInDb = $this->_wordsRepository->getItemsFromDB(['setid' => [$set->id]]);
            $wordsTemp = [];
            foreach ($words as $value) {
                if ($value->id == null) {
                    $word = new Word(null, $set->id, $value->word);
                    $this->_wordsRepository->insertItemToDB($word);
                } elseif ($this->validateWord($value, $set)) {
                    $wordsTemp[] = $value->id;
                    $this->_wordsRepository->updateItemInDB($value);
                }
            }

            foreach ($wordsInDb as $word){
                if(!in_array($word->id, $wordsTemp)){
                    $this->_wordsRepository->deleteItem($word);
                }
            }

            return true;
        }

        return false;
    }

    public function deleteSet(int $setId, User $user): bool
    {
        $setAndWords = $this->getSet($setId);
        if ($this->isUserOwner($setAndWords->set, $user)) {
            $games = $this->_gamesRepository->getItemsFromDB(['setid' => [$setId]]);
            foreach ($games as $game){
                $game->setid = 0;
                $this->_gamesRepository->updateItemInDB($game);
            }
            $words = $setAndWords->words;
            foreach ($words as $word) {
                $this->_wordsRepository->deleteItem($word);
            }
            $this->_setsRepository->deleteItem($setAndWords->set);
            return true;
        }

        return false;
    }

    public function isUserOwner(Set $set, User $user): bool
    {
        if ($this->_usersService->isUserExists($user)) {
            $setFromDB = $this->_setsRepository->getItemFromDB($set->id);
            return $setFromDB->userid == $user->id && $set->userid == $user->id;
        }
        return false;
    }

    public function getWord(int $id): Word
    {
        return $this->_wordsRepository->getItemFromDB($id);
    }

    public function validateWord(Word $word, Set $set): bool
    {
        $wordFromDb = $this->getWord($word->id);
        return $word->setid == $wordFromDb->setid && $set->id == $word->setid;
    }

    public function getRandomWord(object $setAndWords): Word
    {
        return $setAndWords->words[rand(0, count($setAndWords->words) - 1)];
    }

    public function getList(User $user): array
    {
        if ($this->_usersService->isUserExists($user)) {
            return $this->_setsRepository->getItemsFromDB(['userid' => [$user->id]]);
        }

        return [];
    }

    public function getListPublic(): array
    {
        return $this->_setsRepository->getItemsFromDB(['userid' => [0]]);
    }
}

<?php

namespace framework\repositories;

use framework\database\DatabaseRequest;
use framework\utils\ConstantsHelper;
use framework\utils\NumericHelper;
use framework\utils\SqlHelper;
use PDO;

abstract class BaseRepository
{
    protected string $className;
    protected string $tableName;

    public function __construct()
    {
    }
    public function getItemFromDB(int $id)
    {
        $table = $this->tableName;
        $query = "select * from `$table` where id = $id";
        $connection = new DatabaseRequest($query);
        $connection->execute();
        $response = $connection->fetchObject($this->className);
        return $response;
    }
    public function getLastInsertedItem()
    {
        $table = $this->tableName;
        $query = "SELECT * FROM `$table` ORDER BY `id` DESC LIMIT 1";
        $connection = new DatabaseRequest($query);
        $connection->execute();
        $response = $connection->fetchObject($this->className);
        return $response;
    }
    public function getItemsFromDB(array $where = [], ?int $offset = null, ?int $limit = null): array
    {
        $table = $this->tableName;
        if (count($where) != 0) {
            $whereQuery = SqlHelper::whereCreate($where);

            if ($whereQuery != "") {
                $query = "select * from `$table` where $whereQuery";
            } else {
                return [];
            }
        } else {
            $query = "select * from `$table`";
        }

        if ($offset !== null && $limit !== null) {
            $query .= " LIMIT $limit OFFSET $offset";
        }

        $connection = new DatabaseRequest($query);
        $connection->execute();
        $response = $connection->fetchAll(PDO::FETCH_CLASS, $this->className);
        return $response;
    }
    public function insertItemToDB($item): bool
    {
        $table = $this->tableName;
        $query = "INSERT INTO `$table` " . SqlHelper::insertObjects([$item]);
        return (new DatabaseRequest($query))->execute();
    }
    public function updateItemInDB($item): bool
    {
        $table = $this->tableName;
        $query = "UPDATE `$table`
        SET " . SqlHelper::updateObject($item)
            . " WHERE " . SqlHelper::whereCreate([
                'id' => [$item->id]
            ]);
        return (new DatabaseRequest($query))->execute();
    }
    public function deleteItem($item): bool
    {
        $table = $this->tableName;
        $query = "DELETE FROM `$table` WHERE " . SqlHelper::whereCreate([
            'id' => [$item->id]
        ]);
        return (new DatabaseRequest($query))->execute();
    }
    public function count(array $where = []): int
    {
        $table = $this->tableName;
        if (count($where) != 0) {
            $whereQuery = SqlHelper::whereCreate($where);

            if ($whereQuery != "") {
                $query = "select count(*) from `$table` where $whereQuery";
            } else {
                return [];
            }
        } else {
            $query = "select count(*) from `$table`";
        }

        $connection = new DatabaseRequest($query);
        $connection->execute();
        $response = NumericHelper::toInt($connection->fetch()['count(*)']);
        return $response;
    }
}

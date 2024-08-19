<?php

namespace framework\database;

use framework\models\BaseModel;
use framework\utils\ConstantsHelper;
use PDO;

class DatabaseRequest
{
    private PDO $connection;
    private $statement;

    public function __construct(string $query)
    {
        $config = json_decode(file_get_contents(ConstantsHelper::SYSTEM_PATH . "/config.json"));
        $this->connection = new PDO(
            "mysql:host=$config->DB_host; dbname=$config->DB_dbname; charset=utf8",
            $config->DB_username,
            $config->DB_password
        );
        $this->statement = $this->connection->prepare($query);
    }
    public function execute() : bool
    {
        return $this->statement->execute();
    }
    public function fetch($method = PDO::FETCH_ASSOC){
        return $this->statement->fetch($method);
    }
    public function fetchAll($method = PDO::FETCH_CLASS, ?string $class = "stdClass"){
        if($method == PDO::FETCH_CLASS){
            $response = $this->statement->fetchAll(PDO::FETCH_ASSOC);
            $result = [];
            foreach($response as $valueR){
                $preResult = new $class();
                foreach($valueR as $key => $value){
                    if(BaseModel::isJson($value)){
                        $preResult->$key = json_decode($value);
                    } else {
                        $preResult->$key = $value;
                    }
                }
                $result[] = $preResult;
            } 
            return $result;
        } else {
            return $this->statement->fetchAll($method);
        }
    }
    public function fetchObject(?string $class = "stdClass", array $constructorArgs = []){
        $response = $this->fetch();
        $result = new $class();
        foreach($response as $key => $value){
            if(BaseModel::isJson($value)){
                $result->$key = json_decode($value);
            } else {
                $result->$key = $value;
            }
        }

        return $result;
    }
}

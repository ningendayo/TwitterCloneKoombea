<?php

namespace database;

use Exception;
use MongoDB\BSON\ObjectId;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\UpdateResult;

class MongoDatabase
{
    private Client $connection;
    private string $host;
    private int $port;
    private string $user;
    private string $password;
    private string $adminDatabase;
    private array $collationInsensitiveCase;

    public function __construct()
    {
        global $configs;
        $mongoConfig = $configs['mongodb'];
        $this->host = $mongoConfig['host'];
        $this->port = $mongoConfig['port'];
        $this->user = $mongoConfig['user'];
        $this->password = $mongoConfig['password'];
        $this->adminDatabase = $mongoConfig['adminDatabase'];
        $this->collationInsensitiveCase = ['locale' => 'es', 'strength' => 1];
    }

    public function createConnection()
    {
        $this->connection = new Client("mongodb://$this->user:$this->password@$this->host:$this->port/?authSource=$this->adminDatabase&readPreference=primary&ssl=false");
    }

    public function insertData(string $database, string $collection, array $data, $containsData = null)
    {
        $connection = $this->connection;
        $collection = $connection->$database->$collection;
        if ($containsData) {
            $result = $collection->findOne($containsData, ['collation' => $this->collationInsensitiveCase]);
            if ($result) {
                // ya contiene el dato
                return null;
            }
        }
        try {
            $result = $collection->insertOne($data, ['collation' => $this->collationInsensitiveCase]);
        } catch (Exception $exception) {
            return null;
        }
        return $result->getInsertedId();
    }

    public function removeData($database, $collection, array $query): bool
    {
        $connection = $this->connection;
        $collection = $connection->$database->$collection;
        $result = $collection->deleteOne($query);
        return $result->getDeletedCount() > 0;
    }

    public function getCollection($database, $collection): Collection
    {
        $connection = $this->connection;
        return $connection->$database->$collection;
    }

    public function getData($database, $collection, array $query = null)
    {
        $connection = $this->connection;
        $collection = $connection->$database->$collection;
        if (!$query) {
            return $collection->find();
        }
        $result = $collection->find($query, ['collation' => $this->collationInsensitiveCase]);
        return $result->toArray();
    }

    public function update($database, $collection, array $filter, array $instructions): int
    {
        $connection = $this->connection;
        $collection = $connection->$database->$collection;
        $result = $collection->updateOne($filter, $instructions);
        return $result->getModifiedCount();

    }
    public function updateMultiple($database, $collection, array $filter, array $instructions,$options){
        $connection = $this->connection;
        $collection = $connection->$database->$collection;
        $result = $collection->updateMany($filter, $instructions,$options);
        return $result->getModifiedCount();
    }

    public function getConnection(): Client
    {
        return $this->connection;
    }

}
<?php

namespace endpoints;

use \api\Request;
use \api\Response;
use database\AbsDatabase;
use database\Connection;
use database\MongoDatabase;
use database\SQLDatabase;
use DataBaseTypes;


class EndPoint extends AbsDatabase
{

    public Request $request;
    public Response $response;
    private SQLDatabase $sqlDatabase;
    private MongoDatabase $mongoDatabase;
    private array $initializeDatabasesTypes;

    public function __construct(Request $request, Response $response, array $initializeDatabasesTypes)
    {
        $this->request = $request;
        $this->response = $response;
        $this->initializeDatabasesTypes = $initializeDatabasesTypes;
        $this->initializeDatabase();
    }

    private function initializeDatabase()
    {
        foreach ($this->initializeDatabasesTypes as $databasesTypes) {
            if ($databasesTypes == DataBaseTypes::SQLDatabase) {
                $connectionPDO = Connection::createConnection();
                if (!$connectionPDO) {
                    $this->response->addValue('error', [
                        'type' => 'database',
                        'error' => Connection::$errorMessage,
                    ])->printError(Connection::$errorMessage);
                    break;
                }
                $this->sqlDatabase = new SQLDatabase($connectionPDO, $this);
            } else if ($databasesTypes == DataBaseTypes::MongoDB) {
                //
                $this->mongoDatabase = new MongoDatabase();
                $this->mongoDatabase->createConnection();
            }
        }
    }

    public function getSQLDatabase(): SQLDatabase
    {
        // TODO: Implement getMySQL() method.
        return $this->sqlDatabase;
    }

    public function getMongoDB(): MongoDatabase
    {
        // TODO: Implement getMongoDB() method.
        if (!$this->mongoDatabase) {
            $this->response->addValue('error', [
                'type' => 'database',
            ])->printError("No se ha inicializado mongodb en el Endpoint para su utilización");
        }
        return $this->mongoDatabase;
    }
}
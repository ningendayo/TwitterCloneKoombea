<?php

namespace database;

abstract class AbsDatabase
{
    abstract public function getSQLDatabase(): SQLDatabase;

    abstract public function getMongoDB(): MongoDatabase;
}
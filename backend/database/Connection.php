<?php


namespace database;

use PDO;

abstract class Connection
{

    public static PDO $conn;

    public static string $errorMessage = '';

    public static function createConnection(): ?PDO
    {
        global $configs;
        $host = $configs['database']['host'];
        $database = $configs['database']['database'];
        $username = $configs['database']['username'];
        $password = $configs['database']['password'];
        $engine = $configs['database']['engine'];
        try {
            self::$conn = new PDO("$engine:$host[0]=$host[1];$database[0]=$database[1];charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false
            ]);
            return self::$conn;
        } catch (\PDOException $ex) {
            self::$errorMessage = $ex->getMessage();
        }
        return null;
    }

    public static function manageTransaction($status = 'begin')
    {
        switch ($status) {
            case 'begin':
                $result = self::$conn->beginTransaction();
                break;
            case 'commit':
                $result = self::$conn->commit();
                if (!$result) {
                    self::$conn->rollBack();
                }
                break;
            case 'rollback':
                $result = self::$conn->rollBack();
                break;
            default:
                $result = true;
        }
        return $result;
    }

    public static function inTransaction(): bool
    {
        return self::$conn->inTransaction();
    }

}
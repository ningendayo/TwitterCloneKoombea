<?php

namespace database;

use api\DataTypes;
use endpoints\EndPoint;
use PDO;
use PDOStatement;

class SQLDatabase
{
    private EndPoint $endpoint;
    private PDO $conn;

    public function __construct(PDO $conn, EndPoint $endPoint)
    {
        $this->conn = $conn;
        //$this->conn->exec("set names utf8");
        $this->endpoint = $endPoint;
    }

    public function getLastInsertedId(): int
    {
        return $this->conn->lastInsertId();
    }


    private function getFields($field_array): string
    {
        if (count($field_array) == 1 && $field_array[0] == '*') {
            return '*';
        }
        return implode(',', $field_array);
    }

    private function getData($data_array): string
    {
        if (count($data_array) == 1 && $data_array[0] == '*') {
            return '*';
        }
        for ($i = 0; $i < count($data_array); $i++) {
            $data_array[$i] = $this->conn->quote($data_array[$i]);
        }
        return implode(",", $data_array);
    }


    private function fatal($message, $responseCode = 500)
    {
        $this->endpoint->response->addValue('error', [
            'type' => 'database',
            'data' => $this->conn->errorInfo(),
        ])->printError($message, $responseCode);
    }

    public function existsField(array $options, string $table): bool
    {
        $str = '';
        foreach ($options as $key => $val) {

            if (gettype($key) === DataTypes::string) {
                if (gettype($val) === DataTypes::string) {
                    $val = $this->conn->quote($val);
                }
                $str .= $key . '=' . "$val" . ' ';
            } else {
                $str .= $val . ' ';
            }

        }
        $query = "SELECT * FROM $table WHERE $str";
        $result = $this->prepare($query);
        if ($result) {
            return $result->rowCount() != 0;
        }
        if ($this->conn->inTransaction()) {
            $this->conn->rollBack();
        }
        $this->fatal('Error in database while performing existField');
        return false;
    }


    public function prepare(string $query): ?PDOStatement
    {
        try {
            $result = $this->conn->prepare($query);
            $result->execute();
            return $result;
        } catch (\PDOException $ex) {
            $this->fatal($ex->getMessage());
        }
        return null;
    }

    public function prepareStatement(string $query): ?PDOStatement
    {
        try {
            $result = $this->conn->prepare($query);
            return $result;
        } catch (\PDOException $ex) {
            $this->fatal($ex->errorInfo);
        }
        return null;
    }

    public function dbQueryPrepareStatement(string $preparestatement, array $data)
    {
        $result = $this->prepareStatement($preparestatement)->execute($data);
        if (!$result) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $this->fatal('Error in database while performing dbCreate');
        }
    }

    public function dbCreateMultiValues(string $table, array $arrayObjects, callable $onError = null)
    {
        if (sizeof($arrayObjects) == 0) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            if (is_callable($onError)) {
                $onError();
            }
            $this->fatal('Error in database while performing dbCreateMultiValues');
        }
        $str_fields = $this->getFields(array_keys($arrayObjects[0]));
        $str_values = '';
        $total = sizeof($arrayObjects);
        $count = 1;
        foreach ($arrayObjects as $param) {
            $str_data = "(" . $this->getData(array_values($param)) . ")";
            if ($count < $total) {
                $str_data .= ", ";
            }
            $str_values .= $str_data;
            $count += 1;
        }
        $query = "INSERT INTO $table ($str_fields) VALUES $str_values";
        $result = $this->prepare($query);
        if (!$result) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            if (is_callable($onError)) {
                $onError();
            }
            $this->fatal('Error in database while performing dbCreate');
        }
    }

    public function dbCreate(string $table, array $params, callable $onError = null)
    {
        $str_fields = $this->getFields(array_keys($params));
        $str_data = $this->getData(array_values($params));

        $query = "INSERT INTO $table ($str_fields) VALUES ($str_data)";
        $result = $this->prepare($query);
        if (!$result) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            if (is_callable($onError)) {
                $onError();
            }
            $this->fatal('Error in database while performing dbCreate');
        }
    }

    /**
     * @param string $sql_query
     * @return array
     */
    public function dbQuery(string $sql_query): array
    {
        $result = $this->prepare($sql_query);
        if ($result) {
            try {
                return $result->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                return [];
            }
        }
        if ($this->conn->inTransaction()) {
            $this->conn->rollBack();
        }
        $this->fatal('Error in database while performing dbRead');
        return [];
    }


    /**
     * @param array $field_array
     * @param string $condition
     * @return array
     */
    public function dbRead(string $table, array $field_array, string $condition = ''): array
    {
        $str_fields = $this->getFields($field_array);
        $query = "SELECT $str_fields FROM $table $condition";
        $result = $this->prepare($query);
        if ($result) {
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }
        if ($this->conn->inTransaction()) {
            $this->conn->rollBack();
        }
        $this->fatal('Error in database while performing dbRead');
        return [];
    }


    public function dbUpdate(string $table, array $params, string $fieldCondition, string $valueCondition, callable $onError = null)
    {
        $str_fields = array_keys($params);
        $str_data = array_values($params);
        $array_update_data = [];
        for ($i = 0; $i < count($str_data); $i++) {
            if ($str_data[$i] === null || $str_data[$i] === NULL) {
                $array_update_data[$i] = [$str_fields[$i] . "=NULL"];
            } else {
                $array_update_data[$i] = [$str_fields[$i] . "=" . $this->conn->quote($str_data[$i]) . ""];
            }
        }
        $string_data = implode(',', array_map(function ($el) {
            return $el[0];
        }, $array_update_data));
        $valueCondition = $this->conn->quote($valueCondition);
        $query = "UPDATE $table SET $string_data WHERE $fieldCondition = $valueCondition";
        $result = $this->prepare($query);
        if (!$result) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            if (is_callable($onError)) {
                $onError();
            }
            $this->fatal('Error in database while performing dbUpdate');
        }

    }

    public function quote(string $value)
    {
        return $this->conn->quote($value);
    }

    public function doDelete(string $table, string $conditionquery)
    {
        $query = "DELETE FROM $table WHERE $conditionquery";
        $result = $this->prepare($query);
        if (!$result) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $this->fatal('Error in database while performing delete');
        }
    }

    public function dbDelete(string $table, string $fieldCondition, string $valueCondition)
    {
        $valueCondition = $this->conn->quote($valueCondition);
        $query = "DELETE FROM $table WHERE $fieldCondition = $valueCondition";
        $result = $this->prepare($query);
        if (!$result) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $this->fatal('Error in database while performing delete');
        }
    }

    /**
     * This function will return all the possible values for the target enum col of the target table
     * @param string $table
     * @param $col
     * @return array
     */
    public function getEnumValues(string $table, $col): array
    {
        $col = $this->conn->quote($col);
        $res = $this->dbQuery("SHOW COLUMNS FROM `$table` LIKE $col");
        if (count($res) === 0) {
            return [];
        }
        preg_match_all('~\'([^\']*)\'~', $res[0]['Type'], $matches);
        return $matches[1];
    }

    /**
     * This function will check if a value is valid for the target col of the target table when the target col datatype
     * is enum
     * @param string $table
     * @param $col
     * @param $val
     * @return void
     */
    public function checkEnumValueOrDie(string $table, $col, $val): void
    {
        $enumValues = $this->getEnumValues($table, $col);
        if (!in_array($val, $enumValues)) {
            $valids = '[' . implode(',', $enumValues) . ']';
            $this->fatal("The field $col only accepts the next values: $valids", 400);
        }
    }

    /**
     * This function will set the transaction mode in the database engine
     * @return bool Result of the operation true if success false if something went wrong
     */
    public function beginTransaction(): bool
    {
        return $this->conn->beginTransaction();
    }

    /**
     * This function will roll back all operations performed since beginTransaction method was called
     * @return bool Result of the operation true if success false if something went wrong
     */
    public function rollBack(): bool
    {
        return $this->conn->rollBack();
    }

    /**
     * This function will commit all operations performed since beginTransaction method was called
     * @return bool Result of the operation true if success false if something went wrong
     */
    public function commit(): bool
    {
        return $this->conn->commit();
    }

    /**
     * Determine weather the transaction mode in database engine is activated or not
     * @return bool true if the connection object with the database engine is in the transaction satate
     */
    public function inTransaction(): bool
    {
        return $this->conn->inTransaction();
    }

}
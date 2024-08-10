<?php
require_once './utils.php';

class query

{

    public $serverName;
    public $userName;
    public $password;
    public $dbName;
    public $conn;

    public static $joomla2;
    public static $joomla4;
    public static $user_id;

    function __construct($serverName, $userName, $password, $dbName)
    {
        $this->serverName = $serverName;
        $this->userName = $userName;
        $this->password = $password;
        $this->dbName = $dbName;
        $this->conn = new PDO("mysql:host=$this->serverName;dbname=$this->dbName", $this->userName, $this->password);
    }

    function getColumnData($query, $column)
    {

        try {

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("SET NAMES 'utf8mb4'");

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $data = $row[$column];
            }
        } catch (PDOException $e) {
            echo $result . "<br>" . $e->getMessage();
        }
        // $conn = null;
        return $data;
    }

    function getColumnMultiData($query, $column)
    {

        try {

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("SET NAMES 'utf8mb4'");

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $dataArray = [];
            foreach ($result as $row) {
                $data = $row[$column];
                array_push($dataArray, $data);
            }
        } catch (PDOException $e) {
            echo $result . "<br>" . $e->getMessage();
        }
        // $conn = null;
        return $dataArray;
    }

    function Insert($query, $parametters)
    {

        try {

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // set the PDO error mode to exception
            $stmt = $this->conn->prepare($query);

            foreach ($parametters as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        // $conn = null;
    }

    function getAutoIncrement($table_name)
    {
        try {

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("SET NAMES 'utf8mb4'");

            $stmt = $this->conn->prepare("SELECT `AUTO_INCREMENT` FROM  INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$this->dbName' AND TABLE_NAME   = '$table_name'");
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $data = $row["AUTO_INCREMENT"];
            }
        } catch (PDOException $e) {
            echo $result . "<br>" . $e->getMessage();
        }
        if ($data === null) {
            $data = 0;
        }
        return $data;
    }

    function resetAutoIncrement($table_name, $max_asset_id)
    {

        $data = self::getAutoIncrement($table_name);

        $add_auto_increment = utils::addAutoIncrement($data, $max_asset_id) + $data;

        try {

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // set the PDO error mode to exception
            $sql = "alter table $table_name AUTO_INCREMENT = $add_auto_increment";
            // use exec() because no results are returned
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            echo $sql . "<br>" . $e->getMessage();
        }
    }

    function checkExistTable($table_name)
    {

        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("SET NAMES 'utf8mb4'");

            $stmt = $this->conn->prepare("show tables like '%$table_name';");
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $result . "<br>" . $e->getMessage();
        }

        if ($result == null) {
            return null;
        } else {
            return (array_values($result[0])[0]);
        }
    }

    function getDataType($table_name, $column)
    {

        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("SET NAMES 'utf8mb4'");

            $stmt = $this->conn->prepare("show fields from $table_name;");
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $dataArray = [];
            foreach ($result as $row) {
                $data = $row[$column];
                array_push($dataArray, $data);
            }
        } catch (PDOException $e) {
            echo $result . "<br>" . $e->getMessage();
        }
        return $dataArray;
    }

    function defaultQuery($query)
    {

        try {

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // set the PDO error mode to exception
            $sql = $query;
            // use exec() because no results are returned
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            echo $sql . "<br>" . $e->getMessage();
        }
        // $conn = null;
    }
}

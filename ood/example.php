<?php

use function PHPSTORM_META\type;

function getColumnData($query, $column)
{

    $servername = "172.18.0.2";
    $username = "root";
    $password = "root";
    $dbname = "oldLibrary";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);


        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec("SET NAMES 'utf8mb4'");

        $stmt = $conn->prepare($query);
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

$json_str = getColumnData("select * from i0kno_k2_items where id=2010;", "extra_fields");



$json_decode_array = json_decode($json_str);
echo gettype($json_decode_array[0]);
echo "<hr />";
echo count($json_decode_array);
echo "<hr />";
echo print_r($json_decode_array[6]);

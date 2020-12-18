<?php

/*
* database params should be taken from external storage
* omitting since it's a test task
*/
define('MYSQL_HOST', 'localhost');
define('MYSQL_DB', 'k8');
define('MYSQL_USER', 'user');
define('MYSQL_PASSWORD', 'user');
define('MYSQL_CHARSET', 'utf8mb4');


// stackoverflow to the rescue
function get_db_connection($host=MYSQL_HOST, $db=MYSQL_DB, $user=MYSQL_USER, $pass=MYSQL_PASSWORD, $charset=MYSQL_CHARSET){
    
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
    return $pdo;
}

function batch_insert($pdo, $table, $fields, $rows){

    $row_length = count($rows[0]);
    $nb_rows = count($rows);
    $length = $nb_rows * $row_length;

    /* Fill in chunks with '?' and separate them by group of $row_length */
    $args = implode(',', array_map(
        function ($el) {
            return '(' . implode(',', $el) . ')';
        },
        array_chunk(array_fill(0, $length, '?'), $row_length)
    ));

    $params = array();
    foreach ($rows as $row) {
        foreach ($row as $value) {
            $params[] = $value;
        }
    }

    $query = "INSERT INTO $table (".$fields.") VALUES " . $args;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
}

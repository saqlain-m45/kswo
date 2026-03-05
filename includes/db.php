<?php
require_once __DIR__ . '/config.php';

function get_db_connection(): ?mysqli
{
    $connection = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($connection->connect_error) {
        return null;
    }

    $connection->set_charset('utf8mb4');
    return $connection;
}

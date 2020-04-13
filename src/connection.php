<?php

$host = '127.0.0.1';
$db = 'tasks';
$user = 'condottieros';
$pass = '123';
$charset = 'utf8';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$opt = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
return new PDO($dsn, $user, $pass, $opt);





/*
function GetConnection(): PDO
{
    static $conn = null;
    if ($conn) return $conn;
    $host = '127.0.0.1';
    $db = 'tasks';
    $user = 'condottieros';
    $pass = '123';
    $charset = 'utf8';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $opt = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $conn = new PDO($dsn, $user, $pass, $opt);
    return $conn;
}
*/


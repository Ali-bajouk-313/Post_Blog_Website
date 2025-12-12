<?php
$server = "localhost";
$username = "root";
$password = "password";
$db = "project";

try {
    $pdo = new PDO("mysql:host=$server;dbname=$db", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
    echo "Connection to database failed: " . $exception->getMessage();
    die();
}

<?php
$server = "localhost";
$username = "root";
$password = "B@jouk2005";
$db = "projectyoubee";

try {
    $pdo = new PDO("mysql:host=$server;dbname=$db", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
    echo "Connection to database failed: " . $exception->getMessage();
    die();
}

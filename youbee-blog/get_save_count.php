<?php
session_start();
if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)) {
    die("Access Denied");
}
require './connection/connection.php';
$post_id = (int)$_GET['post_id'];
$stmt = $pdo->prepare("SELECT COUNT(*) FROM saves WHERE post_id = :post_id");
$stmt->execute(['post_id' => $post_id]);
echo $stmt->fetchColumn();

<?php
require './connection/connection.php';

if (!isset($_GET['post_id'])) {
    die("Invalid request");
}

$post_id = (int)$_GET['post_id'];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :post_id");
$stmt->execute(['post_id' => $post_id]);
echo $stmt->fetchColumn();

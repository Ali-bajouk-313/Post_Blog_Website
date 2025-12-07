<?php
session_start();
require_once '../connection/connection.php';

if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)) {
    die("Access Denied, please login.");
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $del = $pdo->prepare("DELETE FROM likes WHERE user_id = :user_id AND post_id = :post_id");
        $del->execute([':user_id' => $user_id, ':post_id' => $post_id]);
    } catch (PDOException $e) {
        die("Error deleting post: " . $e->getMessage());
    }
} else {
    die("Invalid Request Method");
}

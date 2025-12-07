<?php
session_start();
require_once './connection/connection.php';

if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)) {
    die("Access Denied");
}

$user_id = (int)$_SESSION['user_id'];
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

if ($post_id <= 0) die("Invalid post");

try {
    $stmt = $pdo->prepare("SELECT 1 FROM likes WHERE user_id = :user_id AND post_id = :post_id");
    $stmt->execute([':user_id' => $user_id, ':post_id' => $post_id]);

    if ($stmt->fetch()) {
        $del = $pdo->prepare("DELETE FROM likes WHERE user_id = :user_id AND post_id = :post_id");
        $del->execute([':user_id' => $user_id, ':post_id' => $post_id]);
        echo "unliked";
    } else {
        $ins = $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (:user_id, :post_id)");
        $ins->execute([':user_id' => $user_id, ':post_id' => $post_id]);
        echo "liked";
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

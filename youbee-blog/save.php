<?php
session_start();
require_once './connection/connection.php';

if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)) {
    die("Access Denied");
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM saves WHERE user_id = :user_id AND post_id = :post_id");
    $stmt->execute([':user_id' => $user_id, ':post_id' => $post_id]);

    if ($stmt->rowCount() > 0) {
        $del = $pdo->prepare("DELETE FROM saves WHERE user_id = :user_id AND post_id = :post_id");
        $del->execute([':user_id' => $user_id, ':post_id' => $post_id]);
        echo "unsaved";
    } else {
        $ins = $pdo->prepare("INSERT INTO saves (user_id, post_id) VALUES (:user_id, :post_id)");
        $ins->execute([':user_id' => $user_id, ':post_id' => $post_id]);
        echo "saved";
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

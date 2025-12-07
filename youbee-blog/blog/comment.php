<?php
session_start();
require_once '../connection/connection.php';

if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)) {
    http_response_code(403);
    echo json_encode(['error' => 'Access Denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_SESSION['user_id'];
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $comment = trim($_POST['comments'] ?? '');

    if ($post_id <= 0 || empty($comment)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO comments (comment, user_id, post_id)
            VALUES (:comment, :user_id, :post_id)
        ");
        $stmt->execute([
            ':comment' => $comment,
            ':user_id' => $user_id,
            ':post_id' => $post_id
        ]);

        // Get the newly inserted comment to return
        $last_id = $pdo->lastInsertId();
        $stmtFetch = $pdo->prepare("
            SELECT c.*, s.username, s.profilelink
            FROM comments c
            JOIN signup s ON c.user_id = s.id
            WHERE c.id = :id
        ");
        $stmtFetch->execute([':id' => $last_id]);
        $newComment = $stmtFetch->fetch(PDO::FETCH_ASSOC);

        echo json_encode($newComment);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database Error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid Request Method']);
}

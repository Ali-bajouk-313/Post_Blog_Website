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
    $post_id = (int)($_POST['post_id'] ?? 0);
    $comment_id = (int)($_POST['comment_id'] ?? 0);
    $reply = trim($_POST['comments'] ?? '');

    if ($post_id <= 0 || $comment_id <= 0 || empty($reply)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO reply_comments (comment, user_id, post_id, comment_id)
            VALUES (:comment, :user_id, :post_id, :comment_id)
        ");
        $stmt->execute([
            ':comment' => $reply,
            ':user_id' => $user_id,
            ':post_id' => $post_id,
            ':comment_id' => $comment_id
        ]);

        // Get the newly inserted reply
        $last_id = $pdo->lastInsertId();
        $stmtFetch = $pdo->prepare("
            SELECT r.*, s.username AS reply_username, s.profilelink AS reply_profilelink
            FROM reply_comments r
            JOIN signup s ON r.user_id = s.id
            WHERE r.id = :id
        ");
        $stmtFetch->execute([':id' => $last_id]);
        $newReply = $stmtFetch->fetch(PDO::FETCH_ASSOC);

        echo json_encode($newReply);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database Error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid Request Method']);
}

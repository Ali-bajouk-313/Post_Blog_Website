<?php
session_start();
require_once '../connection/connection.php';

if (!isset($_SESSION['loggedin'], $_SESSION['is_admin']) || $_SESSION['loggedin'] !== true || $_SESSION['is_admin'] != 1) {
    die('Access denied. Admins only.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request.');
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF validation failed.');
}

$user_id = intval($_POST['user_id']);
$action  = $_POST['action'] ?? '';

$stmt = $pdo->prepare("SELECT email FROM signup WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('User not found.');
}

$self_email = $_SESSION['email'];
if ($user['email'] === $self_email && in_array($action, ['remove_admin', 'delete_user'])) {
    header("Location: ../admin.php?err=1");
    exit;
}

try {
    switch ($action) {
        case 'make_admin':
            $stmt = $pdo->prepare("UPDATE signup SET is_admin = 1 WHERE id = :id");
            $stmt->execute(['id' => $user_id]);
            $msg = "User promoted to admin.";
            break;

        case 'remove_admin':
            $stmt = $pdo->prepare("UPDATE signup SET is_admin = 0 WHERE id = :id");
            $stmt->execute(['id' => $user_id]);
            $msg = "Admin rights removed.";
            break;

        case 'delete_user':
            $stmt = $pdo->prepare("DELETE FROM post WHERE user_id = :id");
            $stmt->execute(['id' => $user_id]);

            $stmt = $pdo->prepare("DELETE FROM signup WHERE id = :id");
            $stmt->execute(['id' => $user_id]);

            $msg = "User and their posts deleted successfully.";
            break;

        default:
            die('Invalid action.');
    }

    header("Location: ../admin.php?msg=" . urlencode($msg));
    exit;
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

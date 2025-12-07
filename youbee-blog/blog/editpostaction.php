<?php
session_start();
require "../connection/connection.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die("User not logged in properly.");
}

if (!isset($_SESSION['user_id'])) {
    die("Error: user_id is missing from session. Please login again.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $post_id   = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $title     = htmlspecialchars(trim($_POST['title']));
    $shortdesc = htmlspecialchars(trim($_POST['short_desc']));
    $content   = htmlspecialchars(trim($_POST['Content']));

    if (empty($title) || empty($shortdesc) || empty($content)) {
        die("Empty Fields");
    }

    $stmt = $pdo->prepare("SELECT imagelink FROM post WHERE id=:post_id AND user_id=:user_id");
    $stmt->execute([':post_id' => $post_id, ':user_id' => $user_id]);
    $imagelink = $stmt->fetchColumn() ?: "";

    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES['image_file']['error'] !== UPLOAD_ERR_OK) die("Error uploading file");
        if ($_FILES['image_file']['size'] == 0) die("Empty file");

        $filename = basename($_FILES['image_file']['name']);
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $targetFile = $targetDir . time() . "_" . $filename;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($fileType, $allowedTypes)) die("Invalid file type");

        if (!move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) die("File not uploaded");

        $imagelink = "uploads/" . basename($targetFile);
    }

    try {
        $sql = "UPDATE post SET title=:title, short_desc=:short_desc, Content=:content, imagelink=:imagelink, created_date=NOW() 
                WHERE id=:post_id AND user_id=:user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':short_desc' => $shortdesc,
            ':content' => $content,
            ':imagelink' => $imagelink,
            ':post_id' => $post_id,
            ':user_id' => $user_id
        ]);

        header("Location: ../author.php?post_id=" . $post_id);
    } catch (PDOException $e) {
        die("Error saving post: " . $e->getMessage());
    }
}

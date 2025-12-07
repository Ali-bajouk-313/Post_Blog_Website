<?php
session_start();
require "../connection/connection.php";

// Check login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die("User not logged in properly.");
}

// Check user_id
if (!isset($_SESSION['user_id'])) {
    die("Error: user_id is missing from session. Please login again.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $subject   = htmlspecialchars(trim($_POST['subject']));
    $shortdesc = htmlspecialchars(trim($_POST['shortdesc']));
    $content   = htmlspecialchars(trim($_POST['content']));

    if (empty($subject) || empty($shortdesc) || empty($content)) {
        header("Location: ../newpost.php?err=1");
        exit();
    }

    $imagelink = "";
    if (isset($_FILES['profile']) && $_FILES['profile']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['profile']['error'] !== UPLOAD_ERR_OK) {
            header("Location: ../newpost.php?err=3");
            exit();
        }

        if ($_FILES['profile']['size'] == 0) {
            header("Location: ../newpost.php?err=4");
            exit();
        }

        $filename = basename($_FILES['profile']['name']);
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $targetFile = $targetDir . time() . "_" . $filename;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ["jpg", "jpeg", "png", "gif"];

        if (!in_array($fileType, $allowedTypes)) {
            header("Location: ../newpost.php?err=2"); // invalid type
            exit();
        }

        if (!move_uploaded_file($_FILES['profile']['tmp_name'], $targetFile)) {
            header("Location: ../newpost.php?err=5"); // failed to move
            exit();
        }

        $imagelink = "uploads/" . basename($targetFile);
    }

    try {
        $sql = "INSERT INTO Post (title, short_desc, content, user_id, imagelink) 
                VALUES (:title, :short_desc, :content, :user_id, :imagelink)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':title', $subject);
        $stmt->bindParam(':short_desc', $shortdesc);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':imagelink', $imagelink);
        $stmt->execute();

        $_SESSION['imagelink'] = $imagelink;
        $last_id = $pdo->lastInsertId();

        header("Location: ../author.php?post_id=" . $last_id);
        exit();
    } catch (PDOException $e) {
        die("Error inserting post: " . $e->getMessage());
    }
}

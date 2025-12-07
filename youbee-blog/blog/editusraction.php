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
    $username = htmlspecialchars(trim($_POST['username']));
    $email    = htmlspecialchars(trim($_POST['email']));

    if (empty($username) || empty($email)) {
        die("Empty Fields");
    }

    $stmt = $pdo->prepare("SELECT profilelink FROM signup WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    $oldImage = $stmt->fetchColumn() ?: "";

    $imagelink = $oldImage;
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image_file']['error'] !== UPLOAD_ERR_OK) die("Error uploading file");
        if ($_FILES['image_file']['size'] == 0) die("Empty file");

        $filename = basename($_FILES['image_file']['name']);
        $targetDir = "../profile/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $targetFile = $targetDir . time() . "_" . $filename;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($fileType, $allowedTypes)) die("Invalid file type");

        if (!move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
            die("File not uploaded");
        }

        $imagelink = "profile/" . basename($targetFile);

        if (!empty($oldImage) && file_exists("../" . $oldImage)) {
            unlink("../" . $oldImage);
        }
    }

    try {
        $sql = "UPDATE signup 
                   SET username=:username, 
                       email=:email, 
                       profilelink=:profilelink 
                 WHERE id=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':username'    => $username,
            ':email'       => $email,
            ':profilelink' => $imagelink,
            ':id'          => $user_id
        ]);

        header("Location: ../author.php?id=" . $user_id);
        exit();
    } catch (PDOException $e) {
        die("Error saving user: " . $e->getMessage());
    }
}

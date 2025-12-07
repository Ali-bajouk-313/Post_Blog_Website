<?php
session_start();
require '../connection/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars($_POST['password']);
    $password_confirmation = htmlspecialchars($_POST['password_confirmation']);
    $email = htmlspecialchars(trim($_POST['email']));

    if (empty($username) || empty($password) || empty($password_confirmation) || empty($email)) {
        header("Location: ../signup.php?err=1");
        exit();
    }

    if ($password !== $password_confirmation) {
        header("Location: ../signup.php?err=6");
        exit();
    }

    if (strlen($password) < 8) {
        header("Location: ../signup.php?err=2");
        exit();
    }

    if (!preg_match('/[A-Z]/', $password)) {
        header("Location: ../signup.php?err=3");
        exit();
    }

    if (!preg_match('/[\W_]/', $password)) {
        header("Location: ../signup.php?err=4");
        exit();
    }

    if (!preg_match('/[0-9]/', $password)) {
        header("Location: ../signup.php?err=5");
        exit();
    }

    $imagelink = "";

    if (isset($_FILES['profile']) && $_FILES['profile']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['profile']['error'] !== UPLOAD_ERR_OK) {
            header("Location: ../signup.php?err=3");
            exit();
        }

        if ($_FILES['profile']['size'] == 0) {
            header("Location: ../signup.php?err=4");
            exit();
        }

        $filename = basename($_FILES['profile']['name']);
        $targetDir = "../profile/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $targetFile = $targetDir . time() . "_" . $filename;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ["jpg", "jpeg", "png", "gif"];

        if (!in_array($fileType, $allowedTypes)) {
            header("Location: ../signup.php?err=2");
            exit();
        }

        if (!move_uploaded_file($_FILES['profile']['tmp_name'], $targetFile)) {
            header("Location: ../signup.php?err=5");
            exit();
        }

        $imagelink = "profile/" . basename($targetFile);
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM signup WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $username, 'email' => $email]);

        if ($stmt->fetch()) {
            header("Location: ../signup.php?err=8");
            exit();
        }

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("INSERT INTO signup (username, password, email, profilelink) 
                               VALUES (:username, :password, :email, :profilelink)");

        if ($stmt->execute([
            'username' => $username,
            'password' => $hashed_password,
            'email' => $email,
            'profilelink' => $imagelink
        ])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            header("Location: ../index.php");
            exit();
        } else {
            header("Location: ../signup.php?err=7");
        }
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    die("Invalid request method.");
}

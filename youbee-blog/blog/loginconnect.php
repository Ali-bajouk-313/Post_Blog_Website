<?php
session_start();
require '../connection/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));

    if (empty($email) || empty($password)) {
        die("Error: All fields are required.");
    }

    $stmt = $pdo->prepare("SELECT * FROM signup WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['loggedin'] = true;
        $_SESSION['is_admin'] = (int)$user['is_admin'];
        header("Location: ../index.php");
        exit();
    } else {
        header("Location: ../login.php?err=1");
    }
} else {
    header("Location: ../login.php");
    exit();
}

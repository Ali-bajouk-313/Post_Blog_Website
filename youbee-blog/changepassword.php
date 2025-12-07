<?php
session_start();
require "./connection/connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $username = $_SESSION['username'] ?? null;

    if ((empty(trim($old_password)) || empty(trim($new_password)) || empty(trim($confirm_password)))) {
        die("Fill empty Blanks");
    }

    if (!$username || !isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        die("User not logged in properly.");
    }

    if ($new_password !== $confirm_password) {
        die("New password and confirm password do not match");
    }

    try {
        $sql = "SELECT password FROM signup WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->execute();
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userRow) {
            die("User not found.");
        }

        if (!password_verify($old_password, $userRow['password'])) {
            die("Old password is incorrect");
        }

        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE signup SET password = :new_password WHERE username = :username";
        $stmt1 = $pdo->prepare($update_sql);
        $stmt1->bindParam(":new_password", $hashed_new_password, PDO::PARAM_STR);
        $stmt1->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt1->execute();

        header("Location: ./author.php");
        exit;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
</head>

<body>
    <a href="author.php" class="btn btn-primary">Back to Posts</a>
    <br><br>

    <form action="changepassword.php" method="post">
        <input type="password" name="old_password" placeholder="Old Password" required><br><br>
        <input type="password" name="new_password" placeholder="New Password" required><br><br>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required><br><br>
        <input type="submit" value="Change Password">
    </form>

</body>

</html>
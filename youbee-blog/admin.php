<?php
session_start();
require_once './connection/connection.php';


if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die('Access denied. Ensure you loggedin or Admin');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    $stmt = $pdo->prepare("SELECT * FROM signup");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Portal</title>
    <style>
        body {
            padding-top: 70px;
        }

        .admin-panel {
            display: grid;
            grid-template-columns: 1fr 120px 120px 120px;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
            transition: background-color 0.3s ease;
        }

        .admin-panel.highlight {
            background-color: #e0f7fa;
        }

        .admin-panel.admin-highlight {
            background-color: #fff9c4;
        }

        .admin-panel h1 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #333;
            word-break: break-word;
        }

        .admin-form input {
            width: 100%;
            padding: 8px 10px;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .admin-form input:hover:not(:disabled) {
            transform: scale(1.05);
        }

        .make-admin {
            background-color: #4caf50;
        }

        .remove-admin {
            background-color: #333;
        }

        .delete-user {
            background-color: #f44336;
        }

        .disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            body {
                padding-top: 100px;
            }

            .admin-panel {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .admin-form input {
                width: 100%;
            }

            .admin-panel h1 {
                font-size: 14px;
                text-align: center;
            }
        }

        .admin-panel:hover {
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div> <?php require_once '../Item/header.php'; ?></div>

    <div>
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user):
                $is_self = ($_SESSION['email'] ?? '') === $user['email'];
                $highlight = $is_self ? 'highlight' : '';
            ?>
                <div class="admin-panel <?= $highlight ?>">
                    <h1><?= htmlspecialchars($user['username']) ?> / <?= htmlspecialchars($user['email']) ?></h1>

                    <form action="./blog/user_action.php" method="post" class="admin-form">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <input type="hidden" name="action" value="make_admin">
                        <input type="submit" value="Make Admin" class="make-admin">
                    </form>

                    <form action="./blog/user_action.php" method="post" class="admin-form">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <input type="hidden" name="action" value="remove_admin">
                        <input type="submit" value="Remove Admin" class="remove-admin <?= $is_self ? 'disabled' : '' ?>" <?= $is_self ? 'disabled' : '' ?>>
                    </form>

                    <form action="./blog/user_action.php" method="post" class="admin-form">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="submit" value="Delete User" class="delete-user <?= $is_self ? 'disabled' : '' ?>" <?= $is_self ? 'disabled' : '' ?>>
                    </form>
                    <?php
                    if (isset($_GET['err'])) {
                        if ($_GET['err'] == 1) {
                            echo "<p style='color:red;'> Action not allowed on yourself.</p>";
                        }
                    }
                    ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </div>
    <?php require_once '../Item/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

</body>

</html>
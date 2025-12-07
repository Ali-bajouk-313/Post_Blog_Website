<?php
session_start();
require_once './connection/connection.php';

if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)) {
    die("Access Denied");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $post_id = (int)$_POST['post_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM saves WHERE user_id=:user_id AND post_id=:post_id");
        $stmt->execute(['user_id' => $user_id, 'post_id' => $post_id]);

        header("Location: saved.php");
        exit();
    } catch (PDOException $e) {
        die("Error updating saved posts: " . $e->getMessage());
    }
}

try {
    $sql = "SELECT 
                p.id, p.title, p.short_desc, p.user_id, p.created_date, p.imagelink
            FROM Post p
            JOIN saves s ON p.id = s.post_id
            WHERE s.user_id = :user_id
            ORDER BY p.id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam('user_id', $user_id);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

function hasUserLiked($post_id, $user_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT 1 FROM likes WHERE post_id=:post_id AND user_id=:user_id");
    $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
    return $stmt->fetchColumn() ? true : false;
}

function getLikeCount($post_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id=:post_id");
    $stmt->execute(['post_id' => $post_id]);
    return $stmt->fetchColumn();
}

function getSaveCount($post_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM saves WHERE post_id=:post_id");
    $stmt->execute(['post_id' => $post_id]);
    return $stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Saved Posts</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/simple-blog-template.css" rel="stylesheet">
    <style>
        .post-card {
            margin-bottom: 30px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .post-card img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .post-card img:hover {
            transform: scale(1.05);
        }

        .post-details {
            flex: 1;
        }

        .post-actions button {
            background: none;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-right: 10px;
        }

        .like-btn.liked svg {
            color: #ed4956;
            transform: scale(1.2);
        }

        .save-btn.saved svg {
            color: #000;
            transform: scale(1.2);
        }
    </style>
</head>

<body>

    <?php require_once '../Item/header.php' ?>

    <div class="container">
        <?php foreach ($posts as $post): ?>
            <br><br><br>
            <div class="post-card">
                <img src="<?php echo htmlspecialchars($post['imagelink'] ?: 'http://placehold.it/120x120'); ?>" alt="Post Image">
                <div class="post-details">
                    <h3><a href="post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                    <p>Posted on <?php echo date("F j, Y, g:i a", strtotime($post['created_date'])); ?></p>
                    <p><?php echo htmlspecialchars($post['short_desc']); ?></p>

                    <div class="post-actions">
                        <button class="like-btn <?php echo hasUserLiked($post['id'], $user_id) ? 'liked' : ''; ?>" data-id="<?php echo $post['id']; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12.1 20.7c-.3-.2-7.4-5.4-9.4-8.3C1.6 9.9 2.1 6.7 4.6 5.2 6.6 4 9.1 4.6 11 6.6l1 1 1-1c1.9-2 4.4-2.6 6.4-1.4 2.5 1.5 3 4.7 1.7 6.9-2 2.9-9.1 8.1-9.1 8.1z" />
                            </svg>
                            <span class="like-count"><?php echo getLikeCount($post['id']); ?></span>
                        </button>

                        <!-- Save toggle form -->
                        <form action="saved.php" method="post" style="display:inline;">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit" class="save-btn saved">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 2h12a1 1 0 0 1 1 1v18l-7-4-7 4V3a1 1 0 0 1 1-1z" />
                                </svg>
                                <span class="save-count"><?php echo getSaveCount($post['id']); ?></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php require_once '../Item/footer.php' ?>

    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        document.querySelectorAll('.like-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const postId = this.dataset.id;
                const countSpan = this.querySelector('span');
                const button = this;

                fetch('like.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `post_id=${postId}`
                    })
                    .then(res => res.text())
                    .then(response => {
                        button.classList.toggle('liked', response === 'liked');
                        fetch('get_like_count.php?post_id=' + postId)
                            .then(res => res.text())
                            .then(count => countSpan.textContent = count);
                    })
                    .catch(err => console.error(err));
            });
        });
    </script>

</body>

</html>
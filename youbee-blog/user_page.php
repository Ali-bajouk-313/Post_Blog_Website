<?php
session_start();
require_once './connection/connection.php';

$loggedin1 = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$user_id   = $loggedin1 ? (int)$_SESSION['user_id'] : 0;
$is_admin  = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

$username = $_POST['username'] ?? ($_GET['username'] ?? '');
if (empty($username)) {
    die("No username specified.");
}

function hasUserLiked($post_id, $user_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT 1 FROM likes WHERE post_id = :post_id AND user_id = :user_id");
    $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
    return $stmt->fetchColumn() ? true : false;
}

function hasUserSaved($post_id, $user_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT 1 FROM saves WHERE post_id = :post_id AND user_id = :user_id");
    $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
    return $stmt->fetchColumn() ? true : false;
}

function getLikeCount($post_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :post_id");
    $stmt->execute(['post_id' => $post_id]);
    return $stmt->fetchColumn();
}

function getSaveCount($post_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM saves WHERE post_id = :post_id");
    $stmt->execute(['post_id' => $post_id]);
    return $stmt->fetchColumn();
}

try {
    $sql = "SELECT Post.*, signup.username, signup.profilelink
            FROM Post 
            JOIN signup ON Post.user_id = signup.id 
            WHERE signup.username = :username
            ORDER BY Post.created_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching posts: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts by <?php echo htmlspecialchars($username); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: #f5f5f5;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }

        h2 {
            margin: 20px 0;
            font-size: 24px;
            color: #222;
            border-bottom: 2px solid #ddd;
            padding-bottom: 8px;
        }

        .return-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 16px;
            background: #337ab7;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
        }

        .return-btn:hover {
            background: #23527c;
        }

        .author-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0 10px 5px;
        }

        .author-img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #337AB7;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }

        .post-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 25px;
            display: flex;
            gap: 18px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            transition: box-shadow 0.2s ease;
        }

        .post-card:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }

        .post-card img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .post-card img:hover {
            transform: scale(1.05);
        }

        .post-details {
            flex: 1;
        }

        .post-details h3 {
            margin: 0 0 6px 0;
            font-size: 20px;
            color: #337ab7;
        }

        .post-details h3 a {
            text-decoration: none;
            color: inherit;
        }

        .post-details h3 a:hover {
            text-decoration: underline;
        }

        .post-details p {
            margin: 6px 0;
            font-size: 14px;
            color: #555;
        }

        .btn {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-default {
            background: #337ab7;
            color: #fff;
            border: none;
        }

        .btn-default:hover {
            background: #23527c;
        }

        .delete-btn {
            background-color: red;
            color: white;
            border-radius: 6px;
            border: none;
            padding: 6px 12px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background-color: #b30000;
        }

        .post-actions {
            margin-top: 8px;
        }

        .post-actions button {
            background: none;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-right: 12px;
            font-size: 14px;
        }

        .like-btn.liked {
            color: #ed4956;
            font-weight: bold;
        }

        .save-btn.saved {
            color: #000;
            font-weight: bold;
        }

        #imageModal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
        }

        #imageModal img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        #imageModal .close {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 40px;
            color: white;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container">
        <a href="index.php" class="return-btn">← Back to Home</a>
        <h2>Posts by <?php echo htmlspecialchars($username); ?></h2>

        <?php foreach ($posts as $post):
            $author_img = !empty($post['profilelink']) ? htmlspecialchars($post['profilelink']) : 'profile/unknown.jpeg';
            $post_img   = !empty($post['imagelink']) ? htmlspecialchars($post['imagelink']) : 'uploads/image.png';
        ?>
            <div class="author-info">
                <a href="user_page.php?username=<?php echo urlencode($post['username']); ?>">
                    <img class="author-img" src="<?php echo $author_img; ?>" alt="Profile">
                </a>
                <h4>Posted by <a style="color:#000;" href="user_page.php?username=<?php echo urlencode($post['username']); ?>">
                        <strong><?php echo htmlspecialchars($post['username']); ?></strong>
                    </a></h4>
            </div>

            <div class="post-card">
                <img src="<?php echo $post_img; ?>" alt="Post Image">
                <div class="post-details">
                    <h3><a href="post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                    <p><em>Posted on <?php echo date("F j, Y, g:i a", strtotime($post['created_date'])); ?></em></p>
                    <p><?php echo htmlspecialchars($post['short_desc']); ?></p>

                    <?php if ($loggedin1): ?>
                        <a class="btn btn-default" href="post.php?id=<?php echo $post['id']; ?>">Read More</a>
                        <div class="post-actions">
                            <button class="like-btn <?php echo hasUserLiked($post['id'], $user_id) ? 'liked' : '' ?>" data-id="<?php echo $post['id']; ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12.1 20.7c-.3-.2-7.4-5.4-9.4-8.3C1.6 9.9 2.1 6.7 4.6 5.2 6.6 4 9.1 4.6 11 6.6l1 1 1-1c1.9-2 4.4-2.6 6.4-1.4 2.5 1.5 3 4.7 1.7 6.9-2 2.9-9.1 8.1-9.1 8.1z" />
                                </svg>
                                <span class="like-count"><?php echo getLikeCount($post['id']); ?></span>
                            </button>

                            <button class="save-btn <?php echo hasUserSaved($post['id'], $user_id) ? 'saved' : '' ?>" data-id="<?php echo $post['id']; ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 2h12a1 1 0 0 1 1 1v18l-7-4-7 4V3a1 1 0 0 1 1-1z" />
                                </svg>
                                <span class="save-count"><?php echo getSaveCount($post['id']); ?></span>
                            </button>
                        </div>

                    <?php else: ?>
                        <strong>Please <a href="login.php">Login</a> or <a href="signup.php">Signup</a> to like or save posts.</strong>
                    <?php endif; ?>

                    <?php if ($is_admin): ?>
                        <form action="index.php" method="post" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $post['id']; ?>">
                            <button type="submit" class="delete-btn">Delete</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        $(document).ready(function() {
            $('.like-btn, .save-btn').click(function() {
                var btn = $(this);
                var postId = btn.data('id');
                var action = btn.hasClass('like-btn') ? 'like' : 'save';

                $.post(action + '.php', {
                    post_id: postId
                }, function(response) {
                    if (response === 'liked' || response === 'saved') {
                        btn.addClass(action === 'like' ? 'liked' : 'saved');
                    } else {
                        btn.removeClass(action === 'like' ? 'liked' : 'saved');
                    }
                    $.get('get_' + action + '_count.php', {
                        post_id: postId
                    }, function(count) {
                        btn.find('span').text(count);
                    });
                });
            });
        });
    </script>
</body>

</html>
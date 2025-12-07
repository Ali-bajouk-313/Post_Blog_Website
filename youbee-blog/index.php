<?php
session_start();
require_once './connection/connection.php';

$loggedin1 = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$user_id = $loggedin1 ? $_SESSION['user_id'] : 0;
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

function getPosts($pdo, $search = '')
{
  $stmt = $pdo->prepare("
        SELECT Post.*, signup.username, signup.profilelink 
        FROM Post 
        JOIN signup ON Post.user_id = signup.id 
        WHERE signup.username LIKE :search
        ORDER BY Post.created_date DESC
    ");
  $stmt->execute(['search' => "%$search%"]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function hasUserLiked($post_id, $user_id)
{
  global $pdo;
  $stmt = $pdo->prepare("SELECT 1 FROM likes WHERE post_id=:post_id AND user_id=:user_id");
  $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
  return $stmt->fetchColumn() ? true : false;
}

function hasUserSaved($post_id, $user_id)
{
  global $pdo;
  $stmt = $pdo->prepare("SELECT 1 FROM saves WHERE post_id=:post_id AND user_id=:user_id");
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

$posts = getPosts($pdo);

if (isset($_POST['delete_id']) && $is_admin) {
  $delete_id = (int)$_POST['delete_id'];
  $stmtdelete = $pdo->prepare("DELETE FROM Post WHERE id = :id");
  $stmtdelete->execute(['id' => $delete_id]);
  header("Location: ./index.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Post Portal</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/simple-blog-template.css" rel="stylesheet">
  <style>
    body {
      background: #f5f5f5;
    }

    .author-info {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }

    .author-img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid #337AB7;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
      cursor: pointer;
    }

    .post-card {
      margin-bottom: 30px;
      padding: 15px;
      border: 1px solid #ddd;
      border-radius: 8px;
      display: flex;
      gap: 15px;
      align-items: center;
      background: #fff;
    }

    .post-card img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 6px;
      cursor: pointer;
      transition: transform 0.3s ease;
    }

    .post-card img:hover {
      transform: scale(1.05);
    }

    .post-details {
      flex: 1;
    }

    .delete-btn {
      background-color: red;
      color: white;
      border-radius: 5px;
      border: none;
      padding: 6px;
    }

    .delete-btn:hover {
      background-color: #e40808ff;
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

    .search-container {
      display: flex;
      justify-content: center;
      margin: 20px 0;
    }

    .search-input {
      width: 300px;
      padding: 12px 20px;
      border: none;
      border-radius: 25px;
      font-size: 16px;
      box-shadow: 0 6px 14px rgba(17, 18, 19, 0.3);
      outline: none;
      transition: all 0.3s ease;
    }

    .search-input:focus {
      box-shadow: 0 6px 14px rgba(66, 239, 255, 0.3);
      text-shadow: 1px 1px 3px rgba(59, 198, 211, 0.5);
    }

    .search-input::placeholder {
      color: rgba(0, 0, 0, 0.5);
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
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

    .username {
      display: none;
    }
  </style>
</head>

<body>
  <?php require_once '../Item/header.php' ?>

  <div class="container">
    <div class="search-container">
      <input type="text" class="search-input" id="searchInput" placeholder="Search posts by username...">
    </div>

    <div id="postsContainer">
      <?php foreach ($posts as $post):
        $author_img = !empty($post['profilelink']) ? htmlspecialchars($post['profilelink']) : 'profile/unknown.jpeg';
        $post_img = !empty($post['imagelink']) ? htmlspecialchars($post['imagelink']) : 'uploads/image.png';
      ?>
        <div class="author-info">
          <img class="author-img" src="<?php echo $author_img; ?>" alt="Profile">
          <a class="username" href="user_page.php?username=<?php echo urlencode($post['username']); ?>">
          </a>
          <h4>
            Posted by
            <a style="color:#000;" href="user_page.php?username=<?php echo urlencode($post['username']); ?>">
              <strong><?php echo htmlspecialchars($post['username']); ?></strong>
            </a>
          </h4>
        </div>

        <div class="post-card">
          <img src="<?php echo $post_img; ?>" alt="Post Image">
          <div class="post-details">
            <h3><a href="post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
            <p>Posted on <?php echo date("F j, Y, g:i a", strtotime($post['created_date'])); ?></p>
            <p><?php echo htmlspecialchars($post['short_desc']); ?></p>
            <?php if ($loggedin1): ?>
              <a class="btn btn-default" href="post.php?id=<?php echo $post['id']; ?>">Read More</a>
              <br>
            <?php else : ?>
              <strong>please <a href="login.php">Login</a> or <a href="signup.php">Signup</a> in order to give your opinion</strong>
            <?php endif ?>
            <br>
            <?php if ($loggedin1): ?>
              <div class="post-actions">
                <button class="like-btn <?php echo hasUserLiked($post['id'], $user_id) ? 'liked' : '' ?>" data-id="<?php echo $post['id']; ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12.1 20.7c-.3-.2-7.4-5.4-9.4-8.3C1.6 9.9 2.1 6.7 4.6 5.2 6.6 4 9.1 4.6 11 6.6l1 1 1-1c1.9-2 4.4-2.6 6.4-1.4 2.5 1.5 3 4.7 1.7 6.9-2 2.9-9.1 8.1-9.1 8.1z" />
                  </svg>
                  <span class="like-count"><?php echo getLikeCount($post['id']); ?></span>
                </button>

                <button class="save-btn <?php echo hasUserSaved($post['id'], $user_id) ? 'saved' : '' ?>" data-id="<?php echo $post['id']; ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2h12a1 1 0 0 1 1 1v18l-7-4-7 4V3a1 1 0 0 1 1-1z" />
                  </svg>
                  <span class="save-count"><?php echo getSaveCount($post['id']); ?></span>
                </button>
              </div>
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
  </div>

  <div id="imageModal">
    <span class="close">&times;</span>
    <img id="modalImg" src="">
  </div>

  <?php require_once '../Item/footer.php' ?>

  <script src="js/jquery.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script>
    $(document).ready(function() {

      $('#searchInput').on('keyup', function() {
        const query = $(this).val();
        $.get('search_posts.php', {
          q: query
        }, function(data) {
          $('#postsContainer').html(data);
          bindImageModal();
          bindLikeSaveButtons();
        });
      });

      function bindLikeSaveButtons() {
        $('.like-btn, .save-btn').off('click').on('click', function() {
          const postId = $(this).data('id');
          const isLike = $(this).hasClass('like-btn');
          const btn = $(this);
          $.post(isLike ? 'like.php' : 'save.php', {
            post_id: postId
          }, function(response) {
            btn.toggleClass(isLike ? 'liked' : 'saved', response == (isLike ? 'liked' : 'saved'));
            $.get('get_' + (isLike ? 'like' : 'save') + '_count.php', {
              post_id: postId
            }, function(count) {
              btn.find('span').text(count);
            });
          });
        });
      }
      bindLikeSaveButtons();

      function bindImageModal() {
        const modal = document.getElementById("imageModal");
        const modalImg = document.getElementById("modalImg");
        const closeBtn = modal.querySelector(".close");
        $(".author-img, .post-card img").off('click').on('click', function() {
          modal.style.display = "flex";
          modalImg.src = $(this).attr('src');
        });
        $(closeBtn).off('click').on('click', function() {
          modal.style.display = "none";
        });
        $(modal).off('click').on('click', function(e) {
          if (e.target == modal) modal.style.display = "none";
        });
      }
      bindImageModal();
    });
  </script>
</body>

</html>
<?php
session_start();
require_once './connection/connection.php';

if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)) {
  die("Access Denied, please login.");
}

$user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
  $delete_id = (int)$_POST['delete_id'];
  try {
    $stmt = $pdo->prepare("DELETE FROM Post WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $delete_id, 'user_id' => $user_id]);
    header("Location: ./author.php");
    exit();
  } catch (PDOException $e) {
    die("Error deleting post: " . $e->getMessage());
  }
}

try {
  $stmtUser = $pdo->prepare("SELECT id, username, email, profilelink FROM signup WHERE id = :user_id");
  $stmtUser->execute(['user_id' => $user_id]);
  $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

  $stmtPosts = $pdo->prepare("SELECT Post.id AS post_id, Post.title, Post.short_desc, Post.Content, 
                                       Post.imagelink, Post.created_date
                                FROM Post
                                WHERE Post.user_id = :user_id
                                ORDER BY Post.created_date DESC");
  $stmtPosts->execute(['user_id' => $user_id]);
  $posts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);
  $post_count = count($posts);
} catch (PDOException $e) {
  die("Error: " . $e->getMessage());
}

$default_profile = 'profile/unknown.jpeg';
$profile_src = (!empty($user['profilelink']) && file_exists($user['profilelink'])) ? htmlspecialchars($user['profilelink']) : $default_profile;
$username_display = !empty($user['username']) ? htmlspecialchars($user['username']) : htmlspecialchars($_SESSION['username'] ?? 'User');
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
    .post-card {
      margin-bottom: 30px;
      padding: 15px;
      border: 1px solid #ddd;
      border-radius: 8px;
      display: flex;
      gap: 15px;
      align-items: center;
    }

    .post-card img,
    .author-img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      cursor: pointer;
      border-radius: 6px;
      transition: transform 0.3s ease;
    }

    .author-img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      margin-right: 12px;
      border: 3px solid #337AB7;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    .author-img:hover,
    .post-card img:hover {
      transform: scale(1.05);
    }

    .post-details {
      flex: 1;
    }

    .delete-btn,
    .btn {
      background-color: red;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      cursor: pointer;
    }

    .delete-btn:hover,
    .btn:hover {
      background-color: darkred;
    }

    .Edit_User,
    .Edit {
      display: none;
      width: 100%;
      padding: 15px;
      border: 1px solid #337AB7;
      border-radius: 8px;
      background-color: #f9f9f9;
      margin-bottom: 30px;
    }

    .Edit_btn {
      background-color: #1a91ff;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      cursor: pointer;
      margin-right: 8px;
    }

    .Edit_btn:hover {
      background-color: #0a5fae;
    }

    #imageModal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.9);
      justify-content: center;
      align-items: center;
    }

    #imageModal img {
      max-width: 90%;
      max-height: 90%;
      border-radius: 8px;
    }

    #imageModal .close {
      position: absolute;
      top: 20px;
      right: 35px;
      color: white;
      font-size: 40px;
      font-weight: bold;
      cursor: pointer;
    }
  </style>
</head>

<body>
  <?php require_once '../Item/header.php'; ?>
  <div class="container">
    <div class="row">
      <div class="col-md-12" style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
        <img class="author-img" src="<?php echo $profile_src; ?>" alt="Profile Image">
        <div>
          <h2>Posted by <a href="#" onclick="openUserEdit()"><?php echo $username_display; ?></a> (<?php echo $post_count; ?>)</h2>
          <button type="button" class="Edit_btn" onclick="openUserEdit()">Edit Profile</button>
        </div>
      </div>

      <div class="Edit_User">
        <h3>Edit Profile</h3>
        <form action="./blog/editusraction.php" method="post" enctype="multipart/form-data">
          <div class="form-group">
            <label>Username</label>
            <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
          </div>
          <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
          <div class="form-group">
            <label>Profile Image</label>
            <input type="file" class="form-control" name="image_file" accept="image/*">
          </div>
          <div><a href="changepassword.php">Change Password</a></div><br>
          <button type="submit" class="btn btn-success">Save Changes</button>
          <button type="button" class="btn btn-secondary" onclick="document.querySelector('.Edit_User').style.display='none'">Cancel</button>
        </form>
      </div>

      <div class="Edit">
        <h3>Edit Post</h3>
        <form action="./blog/editpostaction.php" method="post" enctype="multipart/form-data">
          <input type="hidden" name="post_id" id="edit_post_id">
          <div class="form-group">
            <label>Title</label>
            <input type="text" class="form-control" name="title" id="edit_title" required>
          </div>
          <div class="form-group">
            <label>Short Description</label>
            <textarea class="form-control" name="short_desc" id="edit_desc" required></textarea>
          </div>
          <div class="form-group">
            <label>Content</label>
            <textarea class="form-control" name="Content" id="edit_content" required></textarea>
          </div>
          <div class="form-group">
            <label>Post Image</label>
            <input type="file" class="form-control" name="image_file" accept="image/*">
          </div>
          <button type="submit" class="btn btn-success">Save Changes</button>
          <button type="button" class="btn btn-secondary" onclick="document.querySelector('.Edit').style.display='none'">Cancel</button>
        </form>
      </div>

      <div class="col-md-12">
        <?php if ($post_count > 0): ?>
          <?php foreach ($posts as $post):
            $post_img = (!empty($post['imagelink']) && file_exists($post['imagelink'])) ? htmlspecialchars($post['imagelink']) : 'uploads/image.png';
          ?>
            <div class="post-card">
              <img src="<?php echo $post_img; ?>" alt="Post Image">
              <div class="post-details">
                <h3><a href="post.php?id=<?php echo (int)$post['post_id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                <p>Posted on <?php echo date("F j, Y, g:i a", strtotime($post['created_date'])); ?></p>
                <p><?php echo htmlspecialchars($post['short_desc']); ?></p>
                <button type="button" class="Edit_btn"
                  onclick='openEdit(<?php echo (int)$post['post_id']; ?>,
                                              <?php echo json_encode($post["title"]); ?>,
                                              <?php echo json_encode($post["short_desc"]); ?>,
                                              <?php echo json_encode($post["Content"]); ?>)'>
                  Edit
                </button>
                <form action="author.php" method="post" style="display:inline;" onsubmit="return confirm('Delete this post?');">
                  <input type="hidden" name="delete_id" value="<?php echo (int)$post['post_id']; ?>">
                  <button type="submit" class="delete-btn">Delete</button>
                </form>
                <a class="btn btn-default" href="post.php?id=<?php echo (int)$post['post_id']; ?>" style="margin-left:8px;">Read More</a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No posts found.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div id="imageModal">
    <span class="close">&times;</span>
    <img id="modalImg" src="">
  </div>

  <?php require_once '../Item/footer.php'; ?>
  <script src="js/jquery.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script>
    function openEdit(id, title, desc, content) {
      document.querySelector(".Edit").style.display = "block";
      document.getElementById("edit_post_id").value = id;
      document.getElementById("edit_title").value = title;
      document.getElementById("edit_desc").value = desc;
      document.getElementById("edit_content").value = content;
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }

    function openUserEdit() {
      document.querySelector(".Edit_User").style.display = "block";
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }
    const modal = document.getElementById("imageModal");
    const modalImg = document.getElementById("modalImg");
    const closeBtn = modal.querySelector(".close");
    document.querySelectorAll(".author-img, .post-card img").forEach(img => {
      img.addEventListener("click", () => {
        modal.style.display = "flex";
        modalImg.src = img.src;
      });
    });
    closeBtn.addEventListener("click", () => {
      modal.style.display = "none";
    });
    modal.addEventListener("click", e => {
      if (e.target === modal) modal.style.display = "none";
    });
  </script>
</body>

</html>
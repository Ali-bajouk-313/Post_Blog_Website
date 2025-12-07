<?php
session_start();
require_once './connection/connection.php';

if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)) {
  die("Access Denied, please login.");
}

$user_id = (int)$_SESSION['user_id'];

if (!isset($_GET['id'])) {
  die("No post specified.");
}

$post_id = (int)$_GET['id'];

try {
  // Fetch post
  $stmtPost = $pdo->prepare("
        SELECT Post.*, signup.username, signup.profilelink
        FROM Post
        JOIN signup ON Post.user_id = signup.id
        WHERE Post.id = :post_id
    ");
  $stmtPost->execute(['post_id' => $post_id]);
  $post = $stmtPost->fetch(PDO::FETCH_ASSOC);
  if (!$post) die("Post not found.");

  // Fetch comments with author info
  $stmtComments = $pdo->prepare("
        SELECT c.*, s.username, s.profilelink
        FROM comments c
        JOIN signup s ON c.user_id = s.id
        WHERE c.post_id = :post_id
        ORDER BY c.id ASC
    ");
  $stmtComments->execute(['post_id' => $post_id]);
  $comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);

  // Fetch replies with author info
  $stmtReplies = $pdo->prepare("
        SELECT r.*, s.username AS reply_username, s.profilelink AS reply_profilelink
        FROM reply_comments r
        JOIN signup s ON r.user_id = s.id
        WHERE r.post_id = :post_id
        ORDER BY r.created_at ASC
    ");
  $stmtReplies->execute(['post_id' => $post_id]);
  $replies = $stmtReplies->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Error fetching data: " . $e->getMessage());
}

// Organize replies by comment ID
$repliesByComment = [];
foreach ($replies as $rep) {
  $repliesByComment[$rep['comment_id']][] = $rep;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Post Portal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f2f5f8;
      color: #333;
    }

    .container {
      margin-top: 30px;
      max-width: 900px;
    }

    .post-content {
      font-size: 16px;
      line-height: 1.6;
      color: #444;
      margin-top: 15px;
      white-space: pre-wrap;
      word-wrap: break-word;
      overflow-wrap: break-word;
    }

    .post-card {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      margin-bottom: 30px;
    }

    .post-card img {
      width: 100%;
      max-height: 350px;
      object-fit: cover;
      border-radius: 12px;
      margin-bottom: 20px;
    }

    .comment-section h4 {
      margin-bottom: 20px;
      font-weight: 600;
      color: #555;
    }

    .comment-box {
      display: flex;
      gap: 20px;
      background: #ffffff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
      margin-bottom: 20px;
      transition: transform 0.2s ease;
    }

    .comment-box:hover {
      transform: translateY(-2px);
    }

    .comment-left {
      flex: 2;
    }

    .comment-left img {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      vertical-align: middle;
      margin-right: 10px;
    }

    .comment-left .username {
      font-weight: 600;
      color: #222;
    }

    .comment-left .time {
      color: #999;
      font-size: 13px;
      margin-left: 5px;
    }

    .comment-left p {
      margin-top: 8px;
      line-height: 1.5;
    }

    .replied-comment {
      margin-top: 15px;
      padding-left: 60px;
      position: relative;
      border-left: 3px solid #e0e0e0;
    }

    .replied-comment img {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      margin-right: 8px;
      vertical-align: middle;
    }

    .replied-comment .username {
      font-weight: 500;
      color: #333;
    }

    .replied-comment .time {
      color: #aaa;
      font-size: 12px;
      margin-left: 5px;
    }

    .replied-comment p {
      margin-top: 5px;
    }

    .comment-right {
      flex: 1;
      align-self: flex-start;
    }

    .reply-form textarea {
      width: 100%;
      padding: 10px;
      border-radius: 10px;
      border: 1px solid #ccc;
      font-size: 14px;
      resize: vertical;
    }

    .reply-form button {
      margin-top: 10px;
      background: #0069d9;
      color: #fff;
      border: none;
      padding: 8px 14px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
      transition: background 0.2s ease;
    }

    .reply-form button:hover {
      background: #0056b3;
    }

    .new-comment textarea {
      width: 100%;
      padding: 12px;
      border-radius: 12px;
      border: 1px solid #ccc;
      font-size: 15px;
      resize: vertical;
    }

    .new-comment button {
      margin-top: 10px;
      background: #28a745;
      color: #fff;
      border: none;
      padding: 10px 16px;
      border-radius: 10px;
      cursor: pointer;
      font-weight: 500;
      transition: background 0.2s ease;
    }

    .new-comment button:hover {
      background: #218838;
    }

    a.btn-primary {
      margin-top: 20px;
    }
  </style>
</head>

<body>

  <div class="container">
    <a href="index.php" class="btn btn-primary">Back to Posts</a>

    <div class="post-card">
      <h2><?php echo htmlspecialchars($post['title']); ?></h2>
      <p class="text-muted mb-1">Posted on <?php echo date("F j, Y, g:i a", strtotime($post['created_date'])); ?> by <strong><?php echo htmlspecialchars($post['username']); ?></strong></p>
      <img src="<?php echo htmlspecialchars($post['imagelink'] ?: 'http://placehold.it/120x120'); ?>" alt="Post Image">
      <div class="post-content"><?php echo nl2br(htmlspecialchars($post['Content'])); ?></div>
    </div>

    <div class="comment-section">
      <h4>Comments</h4>
      <?php if (!empty($comments)): ?>
        <?php foreach ($comments as $comment): ?>
          <?php $author_img = !empty($comment['profilelink']) ? htmlspecialchars($comment['profilelink']) : 'profile/unknown.jpeg'; ?>
          <div class="comment-box" data-comment-id="<?php echo $comment['id']; ?>">
            <div class="comment-left">
              <img src="<?php echo $author_img; ?>" alt="User">
              <span class="username"><?php echo htmlspecialchars($comment['username']); ?></span>
              <span class="time"><?php echo date("F j, Y, g:i a", strtotime($comment['created_at'])); ?></span>
              <div class="text">
                <p><?php echo htmlspecialchars($comment['comment']); ?></p>
              </div>

              <?php if (!empty($repliesByComment[$comment['id']])): ?>
                <?php foreach ($repliesByComment[$comment['id']] as $rep): ?>
                  <div class="replied-comment">
                    <img src="<?php echo htmlspecialchars($rep['reply_profilelink']); ?>" alt="Reply User">
                    <span class="username"><?php echo htmlspecialchars($rep['reply_username']); ?></span>
                    <span class="time"><?php echo date("F j, Y, g:i a", strtotime($rep['created_at'])); ?></span>
                    <p><a href="#" style="text-decoration: none;">@<?php echo htmlspecialchars($comment['username']); ?></a> <?php echo htmlspecialchars($rep['comment']); ?></p>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <div class="comment-right">
              <form class="reply-form" action="./blog/reply_message.php" method="post">
                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                <textarea name="comments" rows="4" placeholder="Add a reply..." required></textarea>
                <button type="submit">Reply</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No comments yet.</p>
      <?php endif; ?>
    </div>

    <div class="new-comment mt-4">
      <form id="commentForm" action="./blog/comment.php" method="post">
        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
        <textarea name="comments" rows="3" placeholder="Add a comment..." required></textarea>
        <button type="submit">Comment</button>
      </form>
    </div>

    <a href="index.php" class="btn btn-primary">Back to Posts</a>
  </div>

  <script src="js/jquery.js"></script>
  <script src="js/bootstrap.min.js"></script>

  <script>
    $(document).ready(function() {

      // --- AJAX Add Comment ---
      $('#commentForm').submit(function(e) {
        e.preventDefault();
        let form = $(this);

        $.ajax({
          url: form.attr('action'),
          method: 'POST',
          data: form.serialize(),
          dataType: 'json',
          success: function(data) {
            if (data.error) {
              alert(data.error);
              return;
            }

            let commentBox = $(`
              <div class="comment-box" data-comment-id="${data.id}">
                <div class="comment-left">
                  <img src="${data.profilelink || 'profile/unknown.jpeg'}" alt="User">
                  <span class="username">${$('<div>').text(data.username).html()}</span>
                  <span class="time">${new Date(data.created_at).toLocaleString()}</span>
                  <div class="text"><p>${$('<div>').text(data.comment).html()}</p></div>
                </div>
                <div class="comment-right">
                  <form class="reply-form" action="./blog/reply_message.php" method="post">
                    <input type="hidden" name="post_id" value="${data.post_id}">
                    <input type="hidden" name="comment_id" value="${data.id}">
                    <textarea name="comments" rows="4" placeholder="Add a reply..." required></textarea>
                    <button type="submit">Reply</button>
                  </form>
                </div>
              </div>
            `);


            $('.comment-section').append(commentBox);
            form[0].reset();
            $('html, body').animate({
              scrollTop: commentBox.offset().top
            }, 300);
          },
          error: function() {
            alert('An error occurred while posting the comment.');
          }
        });
      });

      // --- AJAX Add Reply ---
      $(document).on('submit', '.reply-form', function(e) {
        e.preventDefault();
        let form = $(this);
        let parentCommentBox = form.closest('.comment-box');
        let parentCommentUsername = parentCommentBox.find('.comment-left .username').first().text();

        $.ajax({
          url: form.attr('action'),
          method: 'POST',
          data: form.serialize(),
          dataType: 'json',
          success: function(data) {
            if (data.error) {
              alert(data.error);
              return;
            }

            let replyBox = $(`
          <div class="replied-comment">
            <img src="${data.reply_profilelink || 'profile/unknown.jpeg'}" alt="Reply User">
            <span class="username">${$('<div>').text(data.reply_username).html()}</span>
            <span class="time">${new Date(data.created_at).toLocaleString()}</span>
            <p><a href="#" style="text-decoration: none;">@${$('<div>').text(parentCommentUsername).html()}</a> ${$('<div>').text(data.comment).html()}</p>
          </div>
        `);

            parentCommentBox.find('.comment-left').append(replyBox);
            form.find('textarea').val('');
            $('html, body').animate({
              scrollTop: replyBox.offset().top
            }, 300);
          },
          error: function() {
            alert('An error occurred while posting the reply.');
          }
        });
      });

    });
  </script>


</body>

</html>
<?php
session_start();
require_once './connection/connection.php';

$q = isset($_GET['q']) ? $_GET['q'] : '';

$stmt = $pdo->prepare("
    SELECT Post.*, signup.username, signup.profilelink 
    FROM Post 
    JOIN signup ON Post.user_id = signup.id 
    WHERE signup.username LIKE :q
    ORDER BY Post.created_date DESC
");
$stmt->execute(['q' => "%$q%"]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$posts) {
    echo "<p>No posts found.</p>";
    exit;
}

foreach ($posts as $post):
    $author_img = !empty($post['profilelink']) ? htmlspecialchars($post['profilelink']) : 'profile/unknown.jpeg';
    $post_img = !empty($post['imagelink']) ? htmlspecialchars($post['imagelink']) : 'uploads/image.png';
?>
    <div class="author-info">
        <img class="author-img" src="<?php echo $author_img; ?>" alt="Profile">
        <h4>Posted by <strong><?php echo htmlspecialchars($post['username']); ?></strong></h4>
    </div>
    <div class="post-card">
        <img src="<?php echo $post_img; ?>" alt="Post Image">
        <div class="post-details">
            <h3><a href="post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
            <p>Posted on <?php echo date("F j, Y, g:i a", strtotime($post['created_date'])); ?></p>
            <p><?php echo htmlspecialchars($post['short_desc']); ?></p>
        </div>
    </div>
<?php endforeach; ?>
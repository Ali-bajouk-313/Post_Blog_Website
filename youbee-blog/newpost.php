<?php
session_start();

if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)) {
  echo 'Access Denied, please login.<br>';
  echo '<button onclick="window.location.href=\'../login.php\'">Back</button>';
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
</head>

<body>
  <?php require_once '../Item/header.php'; ?>

  <div class="container">
    <div class="row">
      <div class="col-lg-12 newpost">
        <h1>New post</h1>
        <form action="./blog/newpostaction.php" method="post" class="newpost-form" enctype="multipart/form-data">
          <h1>Add an Image for each post</h1>
          <div style="width:fit-content;background-color:#337AB7; color:#FFF; padding:5px; border:none;">
            <input type="file" name="profile" accept="image/*" capture="gallery">
          </div>
          <br>
          <div class="form-group">
            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="shortdesc">Short description</label>
            <input type="text" id="shortdesc" name="shortdesc" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="content">Content</label>
            <textarea rows="5" id="content" name="content" class="form-control" required></textarea>
          </div>

          <input type="submit" value="Post" style="background-color:#337AB7; color:#FFF; padding:5px; border:none;">
        </form>
      </div>
    </div>
    <?php
    if (isset($_GET['err'])) {
      if ($_GET['err'] == 1) {
        echo "<p style='color:red;'>Empty Fields.</p>";
      }
      if ($_GET['err'] == 2) {
        echo "<p style='color:red;'>Unknown File Type.</p>";
      }
      if ($_GET['err'] == 3) {
        echo "<p style='color:red;'>File not found.</p>";
      }
    }
    ?>
  </div>

  <?php require_once '../Item/footer.php'; ?>

  <script src="js/jquery.js"></script>
  <script src="js/bootstrap.min.js"></script>
</body>

</html>
<?php session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="This is demo page made for YouBee.ai's programming courses">
  <meta name="author" content="YouBee.ai">

  <title>Post Portal</title>

  <link href="css/bootstrap.min.css" rel="stylesheet">

  <link href="css/simple-blog-template.css" rel="stylesheet">

</head>

<body>

  <?php require_once '../Item/header.php' ?>


  <div class="container">
    <div class="row">
      <div class="col-lg-2"></div>

      <div class="col-lg-8 signup">
        <h1>Sign up</h1>

        <form action="./blog/signupconnect.php" method="post" class="signup-form" enctype="multipart/form-data">
          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="confirmation">Password Confirmation</label>
            <input type="password" id="confirmation" name="password_confirmation" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" required>
          </div>
          <div style="width:fit-content;background-color:#337AB7; color:#FFF; padding:5px; border:none;">
            <input type="file" name="profile" accept="image/*" capture="gallery">
          </div>
          <button type="submit" class="btn btn-primary">Sign up</button>
        </form>

        <?php
        if (isset($_GET['err'])) {
          if ($_GET['err'] == 1) {
            echo "<p style='color:red;'> Fill in blank.</p>";
          } elseif ($_GET['err'] == 2) {
            echo "<p style='color:red;'>Error: Password must be at least 8 characters long.</p>";
          } elseif ($_GET['err'] == 3) {
            echo "<p style='color:red;'>Error: Password must contain at least one uppercase letter.</p>";
          } elseif ($_GET['err'] == 4) {
            echo "<p style='color:red;'>Error: Password must contain at least one special character.</p>";
          } elseif ($_GET['err'] == 5) {
            echo "<p style='color:red;'>Error: Password must not contain numbers.</p>";
          } elseif ($_GET['err'] == 6) {
            echo "<p style='color:red;'>Error: Password doesn't match.</p>";
          } elseif ($_GET['err'] == 7) {
            echo "<p style='color:red;'>Error: Unable to register</p>";
          }
        }
        if (isset($_GET['registered']) && $_GET['registered'] == 1) {
          echo "<p style='color:green;'>Registration successful! Please log in.</p>";
        }
        ?>

      </div>

      <div class="col-lg-2"></div>
    </div>
  </div>


  <?php require_once '../Item/footer.php' ?>


  <script src="js/jquery.js"></script>
  <script src="js/bootstrap.min.js"></script>

</body>

</html>
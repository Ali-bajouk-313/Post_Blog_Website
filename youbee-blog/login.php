<?php
session_start();

?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="This is demo page made for YouBee.ai's programming courses">
  <meta name="author" content="">

  <title>Post Portal</title>

  <link href="css/bootstrap.min.css" rel="stylesheet">

  <link href="css/simple-blog-template.css" rel="stylesheet">

</head>

<body>

  <?php require_once '../Item/header.php' ?>


  <div class="container">

    <div class="row">

      <div class="col-lg-2"></div>

      <div class="col-lg-8 login">

        <h1>Login</h1>

        <form action="./blog/loginconnect.php" method="post" class="login-form">
          <div class="form-group">
            <label for="Email">Email</label>
            <input type="email" id="email" name="email" class="form-control">
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control">
          </div>

          <button type="submit" class="btn btn-primary">Log in</button>
          <p>Don't have an account? <a href="signup.php">Sign Up Now</a></p>
        </form>
        <?php
        if (isset($_GET['err'])) {
          if ($_GET['err'] == 1) {
            echo "<p style='color:red;'> Failed to login.</p>";
          }
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
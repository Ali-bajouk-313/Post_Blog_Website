<?php

$loggedin1 = false;
$isadmin = false;

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
  $loggedin1 = true;
}

if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
  $isadmin = true;
}

// Example badge counts (you can replace with DB query)
$likes_count = $loggedin1 ? 5 : 0; // Replace 5 with actual user like count
$saved_count = $loggedin1 ? 3 : 0; // Replace 3 with actual user saved count
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Post Portal</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Shadows+Into+Light&display=swap" rel="stylesheet">

  <style>
    .pencil-text {
      font-family: 'Patrick Hand', cursive;
      font-size: 26px;
      color: #444;
      text-shadow: 1px 1px 0 #ccc;
    }

    .nav-text {
      display: none;
      margin-left: 8px;
      font-weight: bold;
      font-size: 14px;
    }

    @media (max-width: 768px) {
      .nav-text {
        display: inline;
      }

      .nav-link svg {
        vertical-align: middle;
        margin-bottom: 2px;
      }
    }

    .navbar-nav li a svg {
      vertical-align: middle;
      margin-bottom: 2px;
    }

    .badge {
      position: absolute;
      top: 0;
      right: -5px;
      background-color: red;
      color: white;
      font-size: 10px;
      font-weight: bold;
      padding: 2px 5px;
      border-radius: 50%;
    }

    .nav-item {
      position: relative;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-1">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand pencil-text" href="index.php">Post Portal</a>
      </div>

      <div class="collapse navbar-collapse" id="navbar-collapse-1">
        <ul class="nav navbar-nav navbar-right">

          <?php if ($loggedin1): ?>
            <?php if ($isadmin): ?>
              <li>
                <a href="admin.php" class="nav-link" title="Admin">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2l7 4v6c0 5-4 9-7 10-3-1-7-5-7-10V6l7-4z"></path>
                    <path d="M12 11v4"></path>
                    <circle cx="12" cy="7" r="1"></circle>
                  </svg>
                  <span class="nav-text">Admin</span>
                </a>
              </li>
            <?php endif; ?>

            <li>
              <a href="index.php" class="nav-link" title="Home">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 11L12 4l9 7" />
                  <path d="M5 10v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V10" />
                  <path d="M9.5 21V14h5v7" />
                </svg>
                <span class="nav-text">Home</span>
              </a>
            </li>

            <li>
              <a href="about.php" class="nav-link" title="About">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"></circle>
                  <line x1="12" y1="16" x2="12" y2="12"></line>
                  <circle cx="12" cy="8" r="1"></circle>
                </svg>
                <span class="nav-text">About</span>
              </a>
            </li>

            <li>
              <a href="newpost.php" class="nav-link" title="New Post">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="3" width="18" height="18" rx="4" ry="4"></rect>
                  <line x1="12" y1="8" x2="12" y2="16"></line>
                  <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
                <span class="nav-text">New Post</span>
              </a>
            </li>

            <li>
              <a href="author.php" class="nav-link" title="Profile">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="8" r="4"></circle>
                  <path d="M6 20c0-3.3 2.7-6 6-6s6 2.7 6 6"></path>
                </svg>
                <span class="nav-text">Profile</span>
              </a>
            </li>

            <li class="nav-item">
              <a href="favorite.php" class="nav-link" title="Like">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>

                <span class="nav-text">Likes</span>
              </a>
            </li>

            <li class="nav-item">
              <a href="saved.php" class="nav-link" title="Saved">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                  <polygon points="19 21 12 17 5 21 5 5 19 5 19 21"></polygon>
                </svg>

                <span class="nav-text">Saved</span>
              </a>
            </li>


            <li>
              <a href="Logout.php" class="nav-link" title="Logout">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                  <polyline points="16 17 21 12 16 7"></polyline>
                  <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span class="nav-text">Logout</span>
              </a>
            </li>

          <?php else: ?>
            <li>
              <a href="about.php" class="nav-link" title="About">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"></circle>
                  <line x1="12" y1="16" x2="12" y2="12"></line>
                  <circle cx="12" cy="8" r="1"></circle>
                </svg>
                <span class="nav-text">About</span>
              </a>
            </li>

            <li>
              <a href="login.php" class="nav-link" title="Login">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M15 3h6v18h-6" />
                  <path d="M10 8l5 4-5 4" />
                </svg>
                <span class="nav-text">Login</span>
              </a>
            </li>

            <li>
              <a href="signup.php" class="nav-link" title="Signup">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="7" r="4" />
                  <path d="M5.5 21c1.5-4 6-4 6-4s4.5 0 6 4" />
                  <line x1="19" y1="8" x2="19" y2="14" />
                  <line x1="16" y1="11" x2="22" y2="11" />
                </svg>
                <span class="nav-text">Signup</span>
              </a>
            </li>

          <?php endif; ?>

        </ul>
      </div>
    </div>
  </nav>


</body>

</html>
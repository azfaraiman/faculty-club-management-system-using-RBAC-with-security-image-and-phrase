<?php
require_once 'connection.php';
session_set_cookie_params([
    'lifetime' => 1800, // Set the session timeout in seconds (e.g., 3600 = 1 hour)
    'path' => '/',
    'domain' => '',
    'secure' => true, // Only transmit the cookie over HTTPS
    'httponly' => true // Restrict cookie access to HTTP only
]);
session_start();

if (!isset($_SESSION['username_student']) || !isset($_SESSION['faculty_student'])) {
    header('Location: ../login.php');
    exit(); // Terminate the script to prevent further execution
}
// Check if the session timeout has been reached
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    // Last activity was more than 30 minutes ago, destroy the session and redirect to login
    session_unset();
    session_destroy();
    echo "<script>alert('Your session has expired. Please log in again.');</script>";
    echo "<script>setTimeout(function(){ window.location.href = '../login.php'; }, 500);</script>";
    exit();
}

// Update last activity time stamp
$_SESSION['LAST_ACTIVITY'] = time();

$user_student = $_SESSION['username_student'];
$faculty_student = $_SESSION['faculty_student'];
?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.0.0/animate.min.css" />
    <link rel="stylesheet" type="text/css" href="../css/style.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro" rel="stylesheet">
    <title>Student</title>
</head>

<body>
    <?php 
    $haha = mysqli_query($conn, "SELECT * FROM `student` WHERE `Student_ID`='$user_student';");
    $showrow = mysqli_fetch_array($haha); ?>

    <div class="wrapper">
        <div class="section">
            <div class="top_navbar">
                <div class="hamburger">
                    <a href="#">
                        <i class="fas fa-bars"></i>
                    </a>
                </div>
                <header>
                    <nav>
                        <ul class="nav__links">
                            <?php 
                            $haha = mysqli_query($conn, "SELECT * FROM student WHERE Student_ID='$user_student';");
                            $showrow = mysqli_fetch_array($haha); ?>
                            <ul class="nav navbar-nav ">
                                <li><a href="user-profile.php"></i> Profile</a></li>
                                <li><a href="logout.php"></i> Logout</a></li>
                            </ul>
                        </ul>
                    </nav>
                </header>
                <div class="sidebar">
                    <div class="profile">
                    <h1 style="color:#fff;">FACLUB UTHM</h1><br><br>
                    <img alt="profile_picture" src="data:image/jpeg;base64,<?php echo base64_encode($showrow['Student_Picture']); ?>" /><br>
                       <h2 style="color:#fff;"><?php echo $showrow['Student_Name']; ?></p>
                       <h2 style="color:#fff;"><?php echo $showrow['Student_ID']; ?></p>
                    </div>
                    <ul>
                    <li>
                            <a href="dashboard.php">
                                <span class="icon"><i class="fas fa-home"></i></span>
                                <span class="item">Dashboard</span>

                            </a>
                        </li>
                        <li>
                            <a href="club-profile.php">
                                <span class="icon"><i class="fas fa-users"></i></span>
                                <span class="item">Club Profile</span>
                            </a>
                        </li>
                        <li>
                            <a href="application.php">
                                <span class="icon"><i class="fas fa-pen"></i></span>
                                <span class="item">Application</span>
                            </a>
                        </li>
                        <li>
                            <a href="interview.php">
                                <span class="icon"><i class="fas fa-clipboard"></i></span>
                                <span class="item">Interview</span>
                            </a>
                        </li>
                        <li>
                            <a href="confirmation.php">
                                <span class="icon"><i class="fas fa-check-double"></i></span>
                                <span class="item">Confirmation</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>


            <div class="col-md-9">
                <div class="content">
                    <?php
                    if (isset($_GET['page'])) {
                        $page = $_GET['page'] . '.php';
                    } else {
                        $page = 'dashboard.php';
                    }

                    if (file_exists($page)) {
                        require_once $page;
                    } else {
                        require_once '404.php';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

  <script type="text/javascript">
    $(document).ready(function() {
  var hamburger = document.querySelector(".hamburger");
  hamburger.addEventListener("click", function() {
    document.querySelector("body").classList.toggle("active");
  });

  // Check if the sidebar is currently open
  if (!document.querySelector("body").classList.contains("active")) {
    // Add the 'active' class only if the sidebar is closed
    document.querySelector("body").classList.add("active");
  }
});
    </script>
</body>

</html>
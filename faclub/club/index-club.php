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

if (!isset($_SESSION['username_club']) || !isset($_SESSION['faculty_club'])) {
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

$user_club = $_SESSION['username_club'];
$faculty_club = $_SESSION['faculty_club'];
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
    <title>Club</title>
</head>

<body>
    <?php
    $haha = mysqli_query($conn, "SELECT f.Faculty_Name as Faculty_Name, c.Club_Email as Club_Email, c.Club_Contact as Club_Contact, c.Club_About as Club_About, 
    c.Club_Name as Club_Name, c.Club_Chart as Club_Chart, c.Club_Logo as Club_Logo FROM club c JOIN faculty f ON c.Faculty_ID = f.Faculty_ID WHERE c.Club_ID ='$faculty_club';");
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
                            $haha = mysqli_query($conn, "SELECT f.Faculty_Name as Faculty_Name, c.Club_Email as Club_Email, c.Club_Contact as Club_Contact, c.Club_About as Club_About, 
                            c.Club_Name as Club_Name, c.Club_Chart as Club_Chart, c.Club_Logo as Club_Logo FROM club c JOIN faculty f ON c.Faculty_ID = f.Faculty_ID WHERE c.Club_ID ='$faculty_club';");
                            $showrow = mysqli_fetch_array($haha); ?>
                            <ul class="nav navbar-nav ">
                                <li><a href="about-contact.php"></i> Profile</a></li>
                                <li><a href="logout.php"></i> Logout</a></li>
                            </ul>
                        </ul>
                    </nav>
                </header>
                <div class="sidebar">
                    <div class="profile">
                    <h1 style="color:#fff;">FACLUB UTHM</h1><br><br>
                        <img alt="profile_picture" src="data:image/jpeg;base64,<?php echo base64_encode($showrow['Club_Logo']); ?>" />
                        <br>
                        <h2 style="color:#fff;"><?php echo $showrow['Club_Name']; ?></h2>
                        <h2 style="color:#fff;"><?php echo $showrow['Faculty_Name']; ?></h2>

                    </div>

                    <ul>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle">
                                <span class="icon"><i class="fas fa-home"></i></span>
                                <span class="item">Club</span>
                                <li><a href="club-profile-club.php">Club Profile</a></li>
                                <li><a href="announcement.php">Announcement</a></li>
                                <li><a href="activities.php">Activities</a></li>
                                <li><a href="about-contact.php">Edit Info</a></li>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle">
                                <span class="icon"><i class="fas fa-home"></i></span>
                                <span class="item">Application</span>
                                <li><a href="status-application.php">Status Application</a></li>
                                <li><a href="list-applicant.php">List of Applicant</a></li>
                                <li><a href="pending-interview.php">Pending Interview</a></li>
                                <li><a href="pending-confirmation.php">Pending Confirmation</a></li>
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
                        $page = 'club-profile-club.php';
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


var slideIndex = 1;
  showSlides(slideIndex);

  function plusSlides(n) {
    showSlides(slideIndex += n);
  }

  function currentSlide(n) {
    showSlides(slideIndex = n);
  }

  function showSlides(n) {
    var i;
    var slides = document.getElementsByClassName("mySlides");
    var dots = document.getElementsByClassName("dot");
    if (n > slides.length) {
      slideIndex = 1
    }
    if (n < 1) {
      slideIndex = slides.length
    }
    for (i = 0; i < slides.length; i++) {
      slides[i].style.display = "none";
    }
    for (i = 0; i < dots.length; i++) {
      dots[i].className = dots[i].className.replace(" active", "");
    }
    slides[slideIndex - 1].style.display = "block";
    dots[slideIndex - 1].className += " active";
  }
    </script>
</body>

</html>
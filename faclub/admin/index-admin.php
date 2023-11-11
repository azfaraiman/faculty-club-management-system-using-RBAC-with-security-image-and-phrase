<?php require_once 'connection.php';
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login_admin.php');
}
$user_admin = $_SESSION['username'];

$query1 = $conn->query("SELECT * FROM admin");
$row1 = $query1->fetch_assoc();
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
    <title>Admin</title>
</head>

<body>
    <?php
    $haha = mysqli_query($conn, "SELECT Admin_Name, Admin_ID, Admin_Picture FROM admin WHERE Admin_ID ='$user_admin';");
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
                           $haha = mysqli_query($conn, "SELECT Admin_Name, Admin_ID, Admin_Picture FROM admin WHERE Admin_ID ='$user_admin';");
                            $showrow = mysqli_fetch_array($haha); ?>
                            <ul class="nav navbar-nav ">
                                <li><a href="logout.php"></i> Logout</a></li>
                            </ul>
                        </ul>
                    </nav>
                </header>
                <div class="sidebar">
                    <div class="profile">
                    <h1 style="color:#fff;">FACLUB UTHM</h1><br><br>
                        <img alt="profile_picture" src="data:image/jpeg;base64,<?php echo base64_encode($showrow['Admin_Picture']); ?>" />
                        <br>
                        <h2 style="color:#fff;"><?php echo $showrow['Admin_ID']; ?></p>
                        <h2 style="color:#fff;"><?php echo $showrow['Admin_Name']; ?></p>
                    </div>
                    <ul>
                        <li>
                            <a href="edit-admin.php">
                                <span class="icon"><i class="fas fa-users"></i></span>
                                <span class="item">Edit Admin</span>
                            </a>
                        </li>
                        <li>
                            <a href="edit-club.php">
                                <span class="icon"><i class="fas fa-pen"></i></span>
                                <span class="item">Edit Club</span>
                            </a>
                        </li>
                        <li>
                            <a href="edit-student.php">
                                <span class="icon"><i class="fas fa-clipboard"></i></span>
                                <span class="item">Accept Student</span>
                            </a>
                        </li>
                        <li>
                            <a href="log-file.php">
                                <span class="icon"><i class="fas fa-table"></i></span>
                                <span class="item">Log Table</span>
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
                        $page = 'log-file.php';
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
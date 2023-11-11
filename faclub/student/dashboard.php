<?php 
require_once 'connection.php';
if (session_status() == PHP_SESSION_NONE) {
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
}

  $corepage = explode('/', $_SERVER['PHP_SELF']);
    $corepage = end($corepage);
    if ($corepage!=='index.php') {
      if ($corepage==$corepage) {
        $corepage = explode('.', $corepage);
       header('Location: index.php?page='.$corepage[0]);
     }
    }
    
    $user_student = $_SESSION['username_student'];
    $faculty_student = $_SESSION['faculty_student'];
?>
<!DOCTYPE html>
	<html lang="en">

	<head>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>


  <!-- Include Slick Slider -->
  <link rel="stylesheet" type="text/css" href="slick-1.8.1/slick/slick.css">
  <script src="slick-1.8.1/slick/slick.min.js"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Edit Profile</title>
	</head>

	<body>


<div class="wrapper">
<div class="announcement-list">
  <div style="margin-top:20px;display: flex; justify-content: space-between; width: 80%" class="container header-container">
        <h1 style="font-size:30px">DASHBOARD</h1>
  </div>
  </div>
  <br>
  <div class="announcement-list">
        <div class="blue-box">
        <h2 style="color:#fff;font-weight:700;">Message</h2>
				<h2 style="color:#fff;">Alert with important notices from club.</h2>
        </div>
    </div><br>
<?php
// Connect to the database


// Retrieve application information
$app_query = "SELECT * FROM application WHERE Student_ID = ?";
$app_stmt = $conn->prepare($app_query);
$app_stmt->bind_param("s", $user_student);
$app_stmt->execute();
$app_result = $app_stmt->get_result();
$application = $app_result->fetch_assoc();

// Retrieve interview information
$int_query = "SELECT * FROM application WHERE Student_ID = ?";
$int_stmt = $conn->prepare($int_query);
$int_stmt->bind_param("s", $user_student);
$int_stmt->execute();
$int_result = $int_stmt->get_result();
$interview = $int_result->fetch_assoc();

// Retrieve confirmation information
$conf_query = "SELECT * FROM application WHERE Student_ID = ?";
$conf_stmt = $conn->prepare($conf_query);
$conf_stmt->bind_param("s", $user_student);
$conf_stmt->execute();
$conf_result = $conf_stmt->get_result();
$confirmation = $conf_result->fetch_assoc();

// Display the information in dashboard modules
?>
<div class="dashboard-container">
	<a href="application.php">
  <div class="dashboard-module <?php echo $application['Application_ID'] ? 'approved' : 'not-approved'; ?>">
    <h1><i class="fas fa-check"></i> Application Status</h1><br>
    <?php if (isset($application['Application_ID'])) { ?>
      <?php if ($application['Application_ID']) { ?>
        <h2>Application Submitted: Yes</h2>
      <?php } else { ?>
        <h2>Application Submitted: No</h2>
      <?php } ?>
    <?php } else { ?>
      <h2>No application</h2>
    <?php } ?>
    <div class="arrow"><i class="fas fa-arrow-right"></i></div>
  </div>
</a>

<a href="interview.php">
  <div class="dashboard-module <?php echo $interview['Approval'] == "Approve" ? 'approved' : ($interview['Approval'] == "Reject" ? 'rejected' : 'not-approved'); ?>">
  <h1><i class="fas fa-calendar-alt"></i> Interview Status</h1><br>
    <?php if (isset($application['Approval'])) { ?>
      <?php if ($application['Approval'] == "Approve") { ?>
        <h2>Interview Approved: Yes</h2>
      <?php } else if ($application['Approval'] == "Reject") { ?>
        <h2>Interview Approved: No</p>
      <?php } else { ?>
        <h2>Interview Approval: Pending</p>
      <?php } ?>
    <?php } else { ?>
      <h2>No application</p>
    <?php } ?>
    <div class="arrow"><i class="fas fa-arrow-right"></i></div>
  </div>
</a>

<a href="confirmation.php">
<div class="dashboard-module <?php echo $confirmation['Confirmation'] == "Confirm" ? 'approved' : ($interview['Confirmation'] == "Reject" ? 'rejected' : 'not-approved'); ?>">
<h1><i class="fas fa-check-double"></i> Confirmation Status</h1><br>
<?php if (isset($application['Confirmation'])) { ?>
      <?php if ($application['Confirmation'] == "Confirm") { ?>
    <h2>Confirmation Confirmed: Yes</h2>
    <?php } else if ($interview['Confirmation'] == "Reject") { ?>
        <h2>Confirmation Confirmed: No</p>
  <?php } else { ?>
    <h2>Confirmation Confirmed: Pending</p>
  <?php } ?>
  <?php } else { ?>
      <h2>No application</p>
    <?php } ?>
  <div class="arrow"><i class="fas fa-arrow-right"></i></div>
</div>
</a>
</div>

<br><br>

<div class="announcement-list">
  <b>Announcement </b><br>
<?php
 


 if (!$conn) {
  die("Connection failed: An error occurred.");
}

$sql = "SELECT n.News_Content as News_Content, n.Modified_On as News_Datetime, c.Club_Name as Club_Name FROM news n JOIN club c ON n.Club_ID = c.Club_ID WHERE n.Club_ID = '$faculty_student';";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $counter = 1; // Initialize the counter variable
    while ($row = mysqli_fetch_assoc($result)) {
      echo "<li class='announcement-item'>";
        echo "<strong>Announcement #" . $counter . "</strong><br>";
        echo "<em>Posted by " . $row["Club_Name"] . " on " . $row["News_Datetime"] . "</em><br>";
        echo "<br>";
        echo $row["News_Content"];
        echo "</li>";
        $counter++; // Increment the counter variable
    }
} else {
    echo "No announcements yet.";
}

?>
</div>
<br><br>
<div class="announcement-list">
  <b style="text-align:center;">Latest Club Activities</b><br>
</div>
<?php
$stmt = mysqli_prepare($conn, "SELECT DISTINCT Media_Description, Date_Time FROM media WHERE Club_ID = ? AND Media_Type NOT IN ('header') ORDER BY Date_Time DESC LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $faculty_student);

if (mysqli_stmt_execute($stmt)) {
  $result = mysqli_stmt_get_result($stmt);

  if ($row = mysqli_fetch_assoc($result)) {
    $description = $row['Media_Description'];
    $time_stamp = $row['Date_Time'];

    // Fetch images for the latest description and timestamp
    $imageStmt = mysqli_prepare($conn, "SELECT Media_Content, Media_Mime FROM media WHERE Club_ID = ? AND Media_Type NOT IN ('header') AND Media_Description = ? AND Date_Time = ?");
    mysqli_stmt_bind_param($imageStmt, "iss", $faculty_student, $description, $time_stamp);
    mysqli_stmt_execute($imageStmt);
    $imageResult = mysqli_stmt_get_result($imageStmt);

    // Check if there are images for the latest description and timestamp
    if (mysqli_num_rows($imageResult) > 0) {
      // Add slideshow container and navigation buttons
      echo '<div class="announcement-list">';
      echo '<div class="image-box-container">';
      echo '<div class="slick-carousel">';

      while ($imageRow = mysqli_fetch_assoc($imageResult)) {
        $media_mime = $imageRow['Media_Mime'];
        $media_content = $imageRow['Media_Content'];

        echo '<div class="image-box">';
        if (strpos($media_mime, 'image') !== false) {
          echo '<img src="data:' . $imageRow['Media_Mime'] . ';base64,' . base64_encode($imageRow['Media_Content']) . '">';
        } else if (strpos($media_mime, 'video') !== false) {
          echo '<video controls><source src="data:' . $media_mime . ';base64,' . base64_encode($media_content) . '" type="' . $media_mime . '"></video>';
        }
        echo '</div>';
      }

      echo '</div>';

      // Display description and timestamp below the slideshow
      echo '<div class="desc">';
      echo '<div class="desc">' . $description . '</div>';
      echo '<div class="time">' . $time_stamp . '</div>';
      echo '</div><br>';
      echo '</div>';
      echo '</div>';

    }
  }
}

mysqli_close($conn);
?>

</div>

<script>
    function slickPrev() {
      jQuery('.slick-carousel').slick('slickPrev');
    }

    function slickNext() {
      jQuery('.slick-carousel').slick('slickNext');
    }

    jQuery('.slick-carousel').slick({
      arrows: true,
      dots: true,
      infinite: true,
      slidesToShow:  4,
      slidesToScroll: 4,
      responsive: [{
          breakpoint: 1024,
          settings: {
            slidesToShow: 3,
            slidesToScroll: 3,
            infinite: true,
            dots: true
          }
        },
        {
          breakpoint: 600,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 2
          }
        },
        {
          breakpoint: 480,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1
          }
        }
      ]
    });
</script>
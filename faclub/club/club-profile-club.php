<?php
require_once 'connection.php';
require_once 'config.php';
require_once 'encryption.php';

if (session_status() == PHP_SESSION_NONE) {
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
}
$corepage = explode('/', $_SERVER['PHP_SELF']);
$corepage = end($corepage);
if ($corepage !== 'index-club.php') {
  if ($corepage == $corepage) {
    $corepage = explode('.', $corepage);
    header('Location: index-club.php?page=' . $corepage[0]);
  }
}
$user_club = $_SESSION['username_club'];
$faculty_club = $_SESSION['faculty_club'];
?>
<?php

$key = ENCRYPTION_KEY;
$iv = substr(INITIALIZATION_VECTOR, 0, 16);

$query1 = mysqli_query($conn, "SELECT * FROM club  WHERE Club_ID ='$faculty_club';");
$row1 = mysqli_fetch_array($query1);

$decrypted_email = decryptData($row1['Club_Email'], $key, $iv);
$decrypted_contact = decryptData($row1['Club_Contact'], $key, $iv);

$query2 = mysqli_query($conn, "SELECT m.Media_Content as Media_Content, m.Media_Description as Media_Description FROM media m JOIN club c ON m.Club_ID = c.Club_ID WHERE m.Club_ID = '$faculty_club';");
$row2 = mysqli_fetch_array($query2);

$query = "SELECT Status_Application FROM club WHERE Club_ID = $faculty_club";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

$status = $row['Status_Application'];

if ($status == 1) {
  $status = "Open";
} else {
  $status = "Close";
}
?>

<!DOCTYPE html>
<html>

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

</head>

<body>
  <div class="wrapper">
  <div class="announcement-list">
    <div style="margin-top: 20px; width: 80%;" class="container header-container-club">
      <div style="display: flex; align-items: center;">
        <h1 style="flex: 0 0 90px;"><img style="width: 90px; height: 90px;" src="data:image/jpeg;base64,<?php echo base64_encode($row1['Club_Logo']); ?>" /></h1>
        <div style="margin-left: 10px;">
          <h1 style="font-size: 30px;word-wrap: break-word;"><?php echo ucwords($row1['Club_Name']); ?></h1>
          <b>Status Club Application: <span style="color: <?php echo ($status == 'Open') ? 'green' : 'red'; ?>;"><?php echo ucwords($status); ?></span></b>
        </div>
      </div>
    </div>
    </div><br>

    <div class="announcement-list">
      <b>Club About</b><br>
      <p style="text-align:justify"><?php echo $row1['Club_About']; ?></p>
    </div><br><br>

    <?php
    $stmt = mysqli_prepare($conn, "SELECT Media_ID, Media_Content, Media_Description, Date_Time, Media_Type FROM media WHERE Club_ID = ? AND Media_Type ='header' ORDER BY Date_Time DESC");
    mysqli_stmt_bind_param($stmt, "i", $faculty_club);
    if (mysqli_stmt_execute($stmt)) {
      $result = mysqli_stmt_get_result($stmt);
      $media_items = mysqli_fetch_all($result, MYSQLI_ASSOC);
    ?>
      <div class="slider">
        <?php foreach ($media_items as $media_item) : ?>
          <div class="slider-item">
            <img src="data:<?php echo $media_item['Media_Type']; ?>;base64,<?php echo base64_encode($media_item['Media_Content']); ?>" alt="<?php echo $media_item['Media_Description']; ?>">
            <div class="slide-info">
              <p>Description: <?php echo $media_item['Media_Description']; ?> - Date: <?php echo $media_item['Date_Time']; ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php
    }
    ?>

<br>
    <div class="announcement-list" style="text-align: center;">
  <table style="margin: 0 auto;">
    <tr>
      <td style="vertical-align: top;">
        <b>Club Vision</b><br>
        <p style="text-align: center;"><?php echo $row1['Club_Vision']; ?></p>
      </td>
      <td style="vertical-align: top;">
        <b>Club Mission</b><br>
        <p style="text-align: center;"><?php echo $row1['Club_Mission']; ?></p>
      </td>
    </tr>
  </table>
</div>
<br><br>

    <div class="announcement-list">
      <b>Announcement </b> <br>
      <?php
      if (!$conn) {
        die("Connection failed: An error occurred.");
    }
    
      $sql = "SELECT n.News_Content as News_Content, n.Modified_On as News_Datetime, c.Club_Name as Club_Name FROM news n JOIN club c ON n.Club_ID = c.Club_ID WHERE  n.Club_ID = '$faculty_club';";
      $result = mysqli_query($conn, $sql);
      if (mysqli_num_rows($result) > 0) {
        $counter = 1;
        while ($row = mysqli_fetch_assoc($result)) {
          echo "<li class='announcement-item'>";
          echo "<strong >Announcement #" . $counter . "</strong><br>";
          echo "<em>Posted by " . $row["Club_Name"] . " on " . $row["News_Datetime"] . "</em><br>";
          echo $row["News_Content"];
          echo "</li>";
          $counter++;
        }
      } else {
        echo "No announcements found.";
      }

      ?>
    </div>
    <br><br>
    <div class="announcement-list">
    <b>Club Contact </b><br>
    <li class='announcement-item'>
      <h2>Club Email: <?php echo $decrypted_email; ?></h2>
      <h2>Club Phone: <?php echo ($decrypted_contact); ?></h2>
      <h2>
    </li>
  </div>
  <br><br>
  <div class="announcement-list">
    <b>Club Social Media</b><br>
    <ul class='announcement-item'>
      <li><a href="<?php echo $row1['Club_Telegram']; ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-telegram"></i> Telegram</a></li>
      <li><a href="<?php echo $row1['Club_Instagram']; ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i> Instagram</a></li>
      <li><a href="<?php echo $row1['Club_Facebook']; ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook"></i> Facebook</a></li>
      <li><a href="<?php echo $row1['Club_Twitter']; ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-twitter"></i> Twitter</a></li>
      <li><a href="<?php echo $row1['Club_Youtube']; ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-youtube"></i> Youtube</a></li>
    </ul>
  </div>
  <br><br>
    <div class="announcement-list">
      <b style="text-align:center;">Activities</b><br>
    </div>
    <?php
$stmt = mysqli_prepare($conn, "SELECT DISTINCT Media_Description, Date_Time FROM media WHERE Club_ID = ? AND Media_Type NOT IN ('header')");
mysqli_stmt_bind_param($stmt, "i", $faculty_club);

if (mysqli_stmt_execute($stmt)) {
  $result = mysqli_stmt_get_result($stmt);

  $slideshowCount = 0; // Variable to keep track of the number of slideshows
  $showMoreButton = false; // Variable to determine if the "See More" button should be shown

  while ($row = mysqli_fetch_assoc($result)) {
    $description = $row['Media_Description'];
    $time_stamp = $row['Date_Time'];

    // Fetch images for the current description and timestamp
    $imageStmt = mysqli_prepare($conn, "SELECT Media_Content, Media_Mime FROM media WHERE Club_ID = ? AND Media_Type NOT IN ('header') AND Media_Description = ? AND Date_Time = ?");
    mysqli_stmt_bind_param($imageStmt, "iss", $faculty_club, $description, $time_stamp);
    mysqli_stmt_execute($imageStmt);
    $imageResult = mysqli_stmt_get_result($imageStmt);

    // Check if there are images for the current description and timestamp
    if (mysqli_num_rows($imageResult) > 0) {
      // Increment the slideshow count
      $slideshowCount++;

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

  <br><br>
  <div class="announcement-list">
    <b style="text-align:center;">Club Chart </b><br>
    <img style="width:50%;" src="data:image/jpeg;base64,<?php echo base64_encode($row1['Club_Chart']); ?>" /><br>

  </div>
  <br><br>
  <
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
      slidesToShow: 4,
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


  <script>
    $(document).ready(function() {
      $('.slider').slick({
        dots: true,
        arrows: true,
        infinite: true,
        autoplay: true,
        autoplaySpeed: 1000, // Updated to 1 second
        speed: 500,
        slidesToShow: 1,
        slidesToScroll: 1
      });
    });

    
    var modal = document.getElementById("myModal");
    var modalImg = document.getElementById("modalImg");
    var captionText = document.getElementById("caption");

    function openModal(contentId) {
      modal.style.display = "block";
      modalImg.src = document.getElementById(contentId).src;
      captionText.innerHTML = document.getElementById(contentId).nextElementSibling.innerHTML;
    }

    function closeModal() {
      modal.style.display = "none";
    }

    window.onclick = function(event) {
      if (event.target === modal) {
        modal.style.display = "none";
      }
    };
  </script>
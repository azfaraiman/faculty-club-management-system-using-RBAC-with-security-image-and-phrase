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
  $corepage = explode('.', $corepage);
  header('Location: index-club.php?page=' . $corepage[0]);
  exit();
}
$user_club = $_SESSION['username_club'];
$faculty_club = $_SESSION['faculty_club'];

if (!$conn) {
  die("Connection failed: An error occurred.");
}


// Retrieve data from database
$sql = "SELECT Media_ID, Media_Content, Media_Description, Date_Time, Media_Type FROM media WHERE Club_ID = ? ORDER BY Media_ID DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $faculty_club);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$stmt = mysqli_prepare($conn, "SELECT Club_Name, Club_About, Club_Contact, Club_Email, Club_Logo, Club_Chart FROM club WHERE Club_ID = ?");
mysqli_stmt_bind_param($stmt, "s", $faculty_club);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $club_name, $club_about, $club_contact, $club_email, $club_logo, $club_chart);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (isset($_POST['delete-media'])) {
  $media_id = $_POST['media_id'];

  // Prepare the database delete statement
  $stmt = mysqli_prepare($conn, "DELETE FROM media WHERE Media_ID = ?");
  mysqli_stmt_bind_param($stmt, "i", $media_id);
  // Execute the database delete statement
  if (mysqli_stmt_execute($stmt)) {
    // Deletion successful
    $logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$user_club', 'Delete Media', CURDATE(), CURTIME())";
				mysqli_query($conn, $logQuery);
    echo "<script>alert('Media deleted successfully.');</script>";
  } else {
    // Deletion failed
    echo "<script>alert('Error deleting media. Please try again.');</script>";
  }
}
if (isset($_POST['update-media'])) {
  $description = $_POST['description'];
  $time_stamp = $_POST['time_stamp'];
  $media_type = $_POST['media_type'];
  
  $successMessageDisplayed = false; // Variable to track if success message has been displayed
  
  // Loop through each selected file
  for ($i = 0; $i < count($_FILES['media']['name']); $i++) {
    $media_file_name = $_FILES['media']['name'][$i];
    $media_file_tmp = $_FILES['media']['tmp_name'][$i];
    $media_file_size = $_FILES['media']['size'][$i];
    $media_file_type = $_FILES['media']['type'][$i];

    // Check if a new media file has been uploaded
    if (!empty($media_file_name)) {
      // Check the file type and size
      $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/avi', 'video/quicktime');
      $max_size = 100 * 1024 * 1024; // 100MB

      if (!in_array($media_file_type, $allowed_types)) {
        echo "<script>alert('Invalid file type. Please upload an image or video file.');</script>";
      } elseif ($media_file_size > $max_size) {
        echo "<script>alert('File is too large. Please upload a smaller image or video file.');</script>";
      } else {
        // Get the binary data of the uploaded media file
        $new_media_file = file_get_contents($media_file_tmp);

        // Prepare the database insert statement
        $stmt = mysqli_prepare($conn, "INSERT INTO media (Club_ID, Media_Content, Media_Description, Date_Time, Media_Type, Media_Mime, Media_FileName) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issssss", $faculty_club, $new_media_file, $description, $time_stamp, $media_type, $media_file_type, $media_file_name);

        // Execute the database insert statement
        if (mysqli_stmt_execute($stmt)) {
          // Insertion successful
          if (!$successMessageDisplayed) { // Display the success message only once
            $logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$user_club', 'Insert Media', CURDATE(), CURTIME())";
				mysqli_query($conn, $logQuery);
            echo "<script>alert('Media updated successfully.');</script>";
            $successMessageDisplayed = true; // Set the flag to true
          }
        } else {
          // Insertion failed
          echo "<script>alert('Error updating media. Please try again.');</script>";
        }
      }
    }
  }
}

?>

<!DOCTYPE html>
<html>

<head>
  <title>Activites</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script>


  var selectedFiles = [];
  function handleFileInputChange(event) {
    const files = event.target.files;

    for (let i = 0; i < files.length; i++) {
      const file = files[i];
      selectedFiles.push(file);

      const fileItem = document.createElement('div');
      fileItem.classList.add('selected-file');

      if (file.type.startsWith('image/')) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.classList.add('selected-image');
        fileItem.appendChild(img);
      }

      const fileName = document.createElement('p');
      fileName.textContent = file.name;
      fileItem.appendChild(fileName);

      document.getElementById('selected-files').appendChild(fileItem);
    }
  }

    // Get the drop zone element
    var dropzone = document.getElementById('dropzone');

    // Add event listeners for drag and drop
    dropzone.addEventListener('dragenter', handleDragEnter, false);
    dropzone.addEventListener('dragover', handleDragOver, false);
    dropzone.addEventListener('dragleave', handleDragLeave, false);
    dropzone.addEventListener('drop', handleDrop, false);

    // Handle drag enter event
    function handleDragEnter(e) {
      e.stopPropagation();
      e.preventDefault();
      dropzone.classList.add('dragover');
    }

    // Handle drag over event
    function handleDragOver(e) {
      e.stopPropagation();
      e.preventDefault();
      e.dataTransfer.dropEffect = 'copy';
    }

    // Handle drag leave event
    function handleDragLeave(e) {
      e.stopPropagation();
      e.preventDefault();
      dropzone.classList.remove('dragover');
    }

    // Handle drop event
    function handleDrop(e) {
      e.stopPropagation();
      e.preventDefault();
      dropzone.classList.remove('dragover');

      var files = e.dataTransfer.files;

      // Check if any files were dropped
      if (files.length > 0) {
        // Set the file input value to the dropped files
        document.getElementById('media').files = files;
      }
    }
  </script>
</head>

<body>
<div class="wrapper">
<div class="announcement-list">
  <div style="margin-top:20px;display: flex; justify-content: space-between; width: 100%" class="container header-container">
    <h1 style="font-size:30px">ACTIVITIES</h1>
    <h3><?php echo $club_name; ?></h3><br>
  </div>
</div>
  <div class="announcement-list">
    <div class="blue-box">
      <h2 style="color:#fff;font-weight:700;">Message</h2>
      <h2 style="color:#fff;">Keep update the club's activities regularly.</h2>
    </div>
  </div><br><br>
  <form action="" method="post" enctype="multipart/form-data">
    <div class="announcement-list">
      <b>Media Type:</b><br>
      <select style="width:100%;"name="media_type" id="media_type">
        <option value="image">Image</option>
        <option value="video">Video</option>
        <option value="header">Header</option>
      </select>
    </div><br>
    <div class="announcement-list">
      <b>Media File:</b><br>
      <div id="dropzone">
        <h2>Drag and drop files here or click to select files</h2><br>
        <input type="file" name="media[]" id="media" accept="image/*,video/*" multiple class="file-input" onchange="handleFileInputChange(event)">
<label for="media" class="file-label">Choose File(s)</label>
    <div id="selected-files"></div>
      </div>
    </div><br>
    <div class="announcement-list">
      <b>Description:</b><br>
      <textarea class="form-control" id="description" name="description" rows="5" placeholder="Enter photo/video description here..." maxlength="1000"></textarea>
    </div><br>
    <div class="announcement-list">
      <b for="date">Date:</b><br>
      <input type="date" name="time_stamp" id="time_stamp"><br>
    </div>
    <div class="announcement-list">
      <input type="submit" name="update-media" class="btn-submit" value="Update" />
    </div>
  </form>

 
  </div><br><br>
  <?php
  echo '<div class="announcement-list">';
  echo '<div class="media-list">';
  $stmt = mysqli_prepare($conn, "SELECT Media_ID, Media_Content, Media_Description, Date_Time, Media_Type, Media_Mime FROM media WHERE Club_ID = ? ORDER BY Date_Time DESC");
  mysqli_stmt_bind_param($stmt, "i", $faculty_club);
  if (mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
      $media_id = $row['Media_ID'];
      $media_content = $row['Media_Content'];
      $description = $row['Media_Description'];
      $time_stamp = $row['Date_Time'];
      $media_mime = $row['Media_Mime'];
      echo '<div class="media-box">';
      // Display the media file
      if (strpos($media_mime, 'image') !== false) {
        echo '<div class="media-image"><img src="data:' . $row['Media_Mime'] . ';base64,' . base64_encode($row['Media_Content']) . '"></div>';
      } else if (strpos($media_mime, 'video') !== false) {
        echo '<div class="media-video"><video controls><source src="data:' . $media_mime . ';base64,' . base64_encode($media_content) . '" type="' . $media_mime . '"></video></div>';
      }

      // Display the media description and time stamp
      echo '<div class="media-info">';
      echo '<h2>' . $description . '</h2>';
      echo '<p>' . $time_stamp . '</p>';
      // Add the delete button
      echo '<form method="post">';
      echo '<input type="hidden" name="media_id" value="' . $media_id . '">';
      echo "<input type='submit' name='delete-media' value='Delete' class='btn-delete'/>";
      echo '</form>';
      echo '<div style="margin-top: 10px;"></div>';
      echo '</div>';
      echo '</div>';
    }
  } else {
    echo "<script>alert('Failed to retrieve media files.');</script>";
  }
  echo '</div>';
  echo '</div>';
  mysqli_stmt_close($stmt);

  mysqli_close($conn);
  ?>
  </div>
  </div>
</body>

</html>
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
    if ($corepage == $corepage) {
        $corepage = explode('.', $corepage);
        header('Location: index-club.php?page=' . $corepage[0]);
    }
}

$user_club = $_SESSION['username_club'];
$faculty_club = $_SESSION['faculty_club'];


$query1 = $conn->query("SELECT Club_ID, Club_Name FROM club c WHERE Club_ID = '$faculty_club'");
$row1 = $query1->fetch_assoc();
// check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // get the value of the open/close radio button
    $status_application = $_POST['status_application'];

    $query = "UPDATE club SET Status_Application = $status_application WHERE Club_ID = $faculty_club";
    mysqli_query($conn, $query);
    $logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$user_club', 'Update Status Application', CURDATE(), CURTIME())";
    mysqli_query($conn, $logQuery);
}

// get the current open/close status of the club from the database

$query = "SELECT Status_Application FROM club WHERE Club_ID = $faculty_club";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$status_application = $row['Status_Application'];
mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Admin Page</title>
</head>
<body>
    <div class="wrapper">
    <div class="announcement-list">
    <div style="margin-top:20px;display: flex; justify-content: space-between; width: 80%" class="container header-container">
        <h1 style="font-size:30px">STATUS APPLICATION </h1>
        <b><?php echo ucwords($row1['Club_Name']); ?></b>
    </div>
    </div>
    <br>
    <div class="announcement-list">
        <div class="blue-box">
        <h2 style="color:#fff;font-weight:700;">Message</h2>
      <h2 style="color:#fff;">Open or close the application when needed only.</h2>
        </div>
    </div><br>
    <div class="announcement-list">
    <form method="post" style="margin-top: 20px;">
        <input type="radio" name="status_application" value="1" <?php echo ($status_application == 1) ? 'checked' : ''; ?>>  Open<br>
        <input type="radio" name="status_application" value="0" <?php echo ($status_application == 0) ? 'checked' : ''; ?>>  Close<br><br>
        <button type="submit" class=btn-submit >Save</button>
    </form><br>

    <?php if ($status_application == 1) { ?>
        <h2 style="font-weight:700;">Club application opened.</p>
    <?php } else { ?>
        <h2 style="font-weight:700;">Club application closed.</p>
    <?php } ?>
    </div>
    </div>
</body>
</html>

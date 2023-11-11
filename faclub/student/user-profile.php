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
if ($corepage !== 'index.php') {
	if ($corepage == $corepage) {
		$corepage = explode('.', $corepage);
		header('Location: index.php?page=' . $corepage[0]);
	}
}

$user_student = $_SESSION['username_student'];
$faculty_student = $_SESSION['faculty_student'];
// Connect to the database


$sql = "SELECT f.Faculty_Name as Faculty_Name, s.Student_Name as Student_Name, s.Student_ID as Student_ID, s.Student_Course as Student_Course, s.Student_Year as Student_Year, 
s.Student_Semester as Student_Semester, s.Student_Email as Student_Email,s.Student_Phone as Student_Phone, s.Student_Picture as Student_Picture 
FROM student s JOIN faculty f ON s.Faculty_ID = f.Faculty_ID WHERE s.Student_ID ='$user_student';";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="wrapper">
<div class="announcement-list">
<div style="margin-top: 20px; width: 80%;" class="container header-container-club">
        <h1 style="font-size:30px">USER PROFILE</h1>
  </div>
  </div>
  <br>
  <div class="announcement-list">
        <div class="blue-box">
        <h2 style="color:#fff;font-weight:700;">Message</h2>
				<h2 style="color:#fff;">Keep update the club's profile regularly.</h2>
        </div>
    </div><br>

<div class="announcement-list">
<div class="table" style="width:70%;">
       
                <table> 
                    <tbody>
                        <tr>
                            <td>Name</td>
                            <td>:</td>
                            <td><?php echo $row['Student_Name']; ?></td>
                        </tr>
                        <tr>
                            <td>Matric Number</td>
                            <td>:</td>
                            <td><?php echo $row['Student_ID']; ?></td>
                        </tr>
                        <tr>
                            <td>Picture</td>
                            <td>:</td>
                            <td><img style = "height:200px; width: 170px" src="data:image/jpeg;base64,<?php echo base64_encode($row['Student_Picture']); ?>" /></td>
                        </tr>
                        <tr>
                            <td>Faculty</td>
                            <td>:</td>
                            <td><?php echo $row['Faculty_Name']; ?></td>
                        </tr>
                        <tr>
                            <td>Course</td>
                            <td>:</td>
                            <td><?php echo $row['Student_Course']; ?></td>
                        </tr>
                        <tr>
                            <td>Year & Semester</td>
                            <td>:</td>
                            <td>Year <?php echo $row['Student_Year']; ?> Semester <?php echo $row['Student_Semester']; ?></td></td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td><?php echo $row['Student_Email']; ?></td>
                        </tr>
                        <tr>
                            <td>Phone</td>
                            <td>:</td>
                            <td><?php echo $row['Student_Phone']; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
</div>
     
       <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.
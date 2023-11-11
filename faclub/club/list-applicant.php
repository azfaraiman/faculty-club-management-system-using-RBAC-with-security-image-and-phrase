<?php
require_once 'connection.php';
if (session_status() == PHP_SESSION_NONE) {
	session_set_cookie_params([
		'lifetime' => 3600, // Set the session timeout in seconds (e.g., 3600 = 1 hour)
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



$query1 = mysqli_query($conn, "SELECT Club_ID,  Club_Name FROM club c WHERE Club_ID = '$faculty_club'");
$row1 = mysqli_fetch_array($query1);
?>
<!DOCTYPE html>
	<html lang="en">
	<head>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro">
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Edit Profile</title>
	</head>
	<body>
<div class="wrapper">
<div class="announcement-list">
<div style="margin-top:20px;display: flex; justify-content: space-between; width: 80%" class="container header-container">
		<h1 style="font-size:30px">LIST OF APPLICANT </h1>
		<b><?php echo ucwords($row1['Club_Name']); ?></b>
	</div>
	</div>
	<br>
    <div class="announcement-list">
			<div class="blue-box">
			<h2 style="color:#fff;font-weight:700;">Message</h2>
      <h2 style="color:#fff;">Keep update the club's profile regularly.</h2>
			</div>
		</div><br><br>

		
    <?php


// check connection
if (!$conn) {
    die("Connection failed: An error occurred.");
}

if (isset($_POST['delete'])) {
	$studentId = $_POST['student_id'];

	// Delete the applicant from the application table
	$deleteApplicationQuery = "DELETE FROM application WHERE Applicant_ID IN (SELECT Applicant_ID FROM applicant WHERE Student_ID = '$studentId')";
	if (mysqli_query($conn, $deleteApplicationQuery)) {
		// Delete the applicant from the applicant table
		$deleteApplicantQuery = "DELETE FROM applicant WHERE Student_ID = '$studentId'";
		if (mysqli_query($conn, $deleteApplicantQuery)) {
			$logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Student_ID, Date, Time) VALUES ('$user_club', 'Delete Applicant', '$studentId', CURDATE(), CURTIME())";
				mysqli_query($conn, $logQuery);
			echo "<script>alert('Applicant deleted successfully!');</script>";
		} else {
			echo "<script>alert('Failed to delete applicant from the applicant table.');</script>";
		}
	} else {
		echo "<script>alert('Failed to delete applicant from the application table.');</script>";
	}
}

// execute query
$sql = "SELECT Student_ID, Applicant_ID, Applicant_Name, Applicant_Course, Applicant_Year, Applicant_Semester, Applicant_Phone, Applicant_Email, Applicant_Resume FROM applicant WHERE Club_ID ='$faculty_club';";
$result = mysqli_query($conn, $sql);

// display table
echo '<div class="announcement-list">';
echo '<table>';
echo '<tr><th>No.</th><th>Student ID</th><th>Name</th><th>Course</th><th>Year</th><th>Semester</th><th>Phone</th><th>Email</th><th>Resume</th><th>Action</th></tr>';
$count = 1;
while ($row = mysqli_fetch_assoc($result)) {
	echo '<form method="post" action="" enctype="multipart/form-data">';
    echo '<tr>';
    echo '<td>' . $count . '</td>';
    echo '<td>' . $row['Student_ID'] . '</td>';
    echo '<td>' . $row['Applicant_Name'] . '</td>';
    echo '<td>' . $row['Applicant_Course'] . '</td>';
    echo '<td>' . $row['Applicant_Year'] . '</td>';
    echo '<td>' . $row['Applicant_Semester'] . '</td>';
    echo '<td>' . $row['Applicant_Phone'] . '</td>';
    echo '<td>' . $row['Applicant_Email'] . '</td>';
    echo '<td><a href="download.php?id=' . $row['Student_ID'] . '">Download</a></td>';
	echo '<td>
                    <form method="post">
                        <input type="hidden" name="student_id" value="' . $row['Student_ID'] . '">
                        <button class="btn-delete-table" type="submit" name="delete">Delete</button>
                    </form>
                </td>';
            echo '</tr>';
    echo '</tr>';
	echo '</form>';
    $count++;
}
echo '</table>';
echo'</div>';
// close connection
mysqli_close($conn);

?>
		
		<script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript">
			
		</script>
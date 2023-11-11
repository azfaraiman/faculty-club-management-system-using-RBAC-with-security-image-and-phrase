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
?>
<?php
$stmt = mysqli_prepare($conn, "SELECT Club_Name, Club_About, Club_Contact, Club_Email, Club_Logo, Club_Chart FROM club WHERE Club_ID = ?");
mysqli_stmt_bind_param($stmt, "s", $faculty_club);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $club_name, $club_about, $club_contact, $club_email, $club_logo, $club_chart);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
// Check connection
if (!$conn) {
    die("Connection failed: An error occurred.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	// Check if the delete button was clicked
	if (isset($_POST['delete'])) {
		$announcement_id = $_POST['announcement_id'];
		// Delete the announcement from the database
		$sql = "DELETE FROM news WHERE News_ID='$announcement_id'";
		if (mysqli_query($conn, $sql)) {
			$logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$user_club', 'Delete Announcement', CURDATE(), CURTIME())";
				mysqli_query($conn, $logQuery);
			echo '<script>alert("Announcement deleted successfully.");</script>';
			exit();
		} else {
			echo "<script>alert('Error deleting announcement.');</script>";
		}
	}

	if (isset($_POST['submit'])) { // The form was submitted to add a new announcement
		// Get the announcement message from the form
		$message = isset($_POST['message']) ? $_POST['message'] : '';


		if (empty($message) || empty($faculty_club)) {
			echo "<script>alert('Please fill in all fields.');</script>";
		} else {
			// Prepare the SQL statement
			$stmt = mysqli_prepare($conn, "INSERT INTO news (Club_ID, News_Content) VALUES (?, ?)");
			mysqli_stmt_bind_param($stmt, "ss", $faculty_club, $message);

			// Execute the prepared statement
			if (mysqli_stmt_execute($stmt)) {
				// If the announcement was successfully inserted, show a success message
				$logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$user_club', 'Insert Announcement', CURDATE(), CURTIME())";
				mysqli_query($conn, $logQuery);
				echo "<script>alert('Announcement submitted successfully!');</script>";
				exit();
			} else {
				// If there was an error inserting the announcement, show an error message
				echo "<script>alert('Error: Announcement submission failed.');</script>";
			}

			// Close the prepared statement
			mysqli_stmt_close($stmt);
		}
	}
}


$sql = "SELECT n.News_ID as News_ID, n.News_Content as News_Content, n.Modified_On as News_Datetime, c.Club_Name as Club_Name 
        FROM news n JOIN club c ON n.Club_ID = c.Club_ID WHERE n.Club_ID = '$faculty_club' ORDER BY n.Modified_On DESC";
$result = mysqli_query($conn, $sql);

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html>

<head>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title>ANNOUNCEMENT</title>
</head>

<body>
	<div class="wrapper">
		<div class="announcement-list">
			<div style="margin-top:20px;display: flex; justify-content: space-between; width: 80%" class="container header-container">
				<h1 style="font-size:30px">ANNOUNCEMENT</h1>
				<b><?php echo $club_name; ?></b><br>
			</div>
		</div>
		<div class="announcement-list">
			<div class="blue-box">
				<h2 style="color:#fff;font-weight:700;">Message</h2>
				<h2 style="color:#fff;">Keep update the club's announcement regularly.</h2>
			</div>
		</div><br><br>
		<div class="announcement-list">
			<b>Add New Announcement</b><br>
		</div>
			<form name="frmContact" id="frmContact" method="post" action="" enctype="multipart/form-data" onsubmit="return validateContactForm()">
			<div class="announcement-list">
				<textarea class="form-control" id="message" name="message" rows="5" placeholder="Enter your message here..." maxlength="1000"></textarea><br>
				<input type="submit" name="submit" class="btn-submit" value="Submit" />
			</div>
			</form>
		

		<br><br>
		<div class="announcement-list">
			<b>Existing Announcement </b> <br>

			<?php
			if (mysqli_num_rows($result) > 0) {
				$num_rows = mysqli_num_rows($result);
				$i = $num_rows;
				$counter = $i; // Initialize the counter variable
				while ($row = mysqli_fetch_assoc($result)) {
					echo "<li class='announcement-item'>";
					echo "<b>Announcement " . $counter . "</b><br>";
					echo "<em>Posted by " . $row["Club_Name"] . " on " . $row["News_Datetime"] . "</em><br><br>";
					echo "<h2>" . $row["News_Content"] . "</h2>";
					echo "<form action='' method='post'>";
					echo "<input type='hidden' name='announcement_id' value='" . $row['News_ID'] . "'/>";
					echo "<br>";
					echo "<input type='submit' name='delete' value='Delete' class='btn-delete'/>";
					echo "</form>";
					echo "</li>";
					$counter--; // Increment the counter variable
				}
			} else {
				echo "No announcements found.";
			}
			?>
		</div>
	</div>

</body>

</html>
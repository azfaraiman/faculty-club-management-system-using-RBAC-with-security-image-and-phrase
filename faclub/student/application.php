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

$query1 = "SELECT s.*, c.Club_ID as Club_ID, c.Club_Name as Club_Name, c.Status_Application as Status_Application 
           FROM student s 
           LEFT JOIN club c ON s.Faculty_ID = c.Faculty_ID 
           WHERE s.Student_ID = '$user_student'";
$row1 = mysqli_fetch_array(mysqli_query($conn, $query1));

$query2 = "SELECT c.Faculty_ID as Faculty_ID, f.Faculty_Name as Faculty_Name, c.Club_Email as Club_Email, c.Club_Contact as Club_Contact, c.Club_About as Club_About, 
           c.Club_Name as Club_Name, c.Club_Chart as Club_Chart, c.Club_Logo as Club_Logo 
           FROM club c 
           JOIN faculty f ON c.Faculty_ID = f.Faculty_ID 
           WHERE c.Club_ID ='$faculty_student'";

$row2 = mysqli_fetch_array(mysqli_query($conn, $query2));

$query = "SELECT Status_Application FROM club WHERE Club_ID = $faculty_student";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

$status = $row['Status_Application'];

if ($status == 1) {
  $status = "Open";
} else {
  $status = "Close";
}




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = $_POST['name'];
	$matric_number = $_POST['matric_number'];
	$faculty = $_POST['faculty'];
	$course = $_POST['course'];
	$year = $_POST['year'];
	$semester = $_POST['semester'];
	$phonenumber = $_POST['phone_number'];
	$email = $_POST['email'];
	$resume = $_FILES['resume'];

	$errors = array();

	// Validate form data
	if (empty($course)) {
		$errors[] = "Course is required";
	}
	if (empty($year)) {
		$errors[] = "Year is required";
	}
	if (empty($semester)) {
		$errors[] = "Semester is required";
	}
	if (empty($phonenumber)) {
		$errors[] = "Phone number is required";
	}
	if (empty($email)) {
		$errors[] = "Email is required";
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$errors[] = "Invalid email format";
	}
	if (empty($resume['name'])) {
		$errors[] = "Resume is required";
	}
	if (empty($errors)) {
		// File upload configuration
		$target_dir = "uploads/";
		if (!file_exists($target_dir)) {
			mkdir($target_dir, 0777, true);
		}
	
		$target_file = $target_dir . basename($resume["name"]);
		$uploadOk = 1;
		$fileSize = $resume["size"];
		$fileContent = file_get_contents($resume["tmp_name"]);
	
		// Check if the file is a PDF
		$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
		if ($imageFileType !== "pdf") {
			$errors[] = "Sorry, only PDF files are allowed.";
			$uploadOk = 0;
		}
	
		// Check file size (max 100MB)
		if ($fileSize > 104857600) {
			$errors[] = "Sorry, your file is too large. Maximum file size is 100MB.";
			$uploadOk = 0;
		}
	
		if ($uploadOk == 1) {
			// Upload file
			if (file_put_contents($target_file, $fileContent) !== false) {
				// Insert application details into the database
				$insert_applicant_query = "INSERT INTO applicant (Student_ID, Club_ID, Applicant_Name,  
					Faculty_ID, Applicant_Course, Applicant_Year, Applicant_Semester, 
					Applicant_Phone, Applicant_Email, Applicant_Resume) 
					VALUES ('$user_student', '$faculty_student', '$name', '$faculty', 
					'$course', '$year', '$semester', '$phonenumber', '$email', 
					UNHEX('" . bin2hex($fileContent) . "'))";
		
				if ($conn->query($insert_applicant_query) === TRUE) {
					// Get the auto-generated Applicant_ID from the previous insert
					$applicant_id = $conn->insert_id;
		
					// Insert application data
					$insert_application_query = "INSERT INTO application (Student_ID, Club_ID, Applicant_ID, Approval, Confirmation) 
						VALUES ('$user_student', '$faculty_student', '$applicant_id', 'Pending', 'Pending')";
		
					if ($conn->query($insert_application_query) === TRUE) {
						$logQuery = "INSERT INTO log_table (Student_ID, Club_ID, Log_Type, Date, Time) VALUES ('$user_student','$faculty_student', 'Submit Application', CURDATE(), CURTIME())";
						mysqli_query($conn, $logQuery);
						echo "<script>alert('Application submitted successfully!');</script>";
					} else {
						echo "<script>alert('Application failed!');</script>";
					}
				} else {
					echo "<script>alert('Application failed!');</script>";
				}
			} else {
				$errors[] = "Sorry, there was an error uploading your file.";
			}
		}
		
	}
	
	if (!empty($errors)) {
		foreach ($errors as $error) {
			echo $error . "<br>";
		}
	}
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-Z/o1zeKj/av/IJGzmqcWnwYlPmdo+Jx5B5O5GZxlNp+MY0ilLLGNGf22cezEesWXMjv2z7Dd+y5ZOAVKr5jKpA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Application</title>
</head>

<body>
	<div class="wrapper">
	<div class="announcement-list">
    <div style="margin-top: 20px; width: 80%;" class="container header-container-club">
      <div style="display: flex; align-items: center;">
        <div style="margin-left: 10px;">
		<h1 style="font-size:30px">REGISTER CLUB </h1>
		<b><?php echo ucwords($row1['Club_Name']); ?></b><br><br>
          <b>Status Club Application: <span style="color: <?php echo ($status == 'Open') ? 'green' : 'red'; ?>;"><?php echo ucwords($status); ?></span></b><br>
        </div>
      </div>
    </div>
    </div><br>
		<div class="announcement-list">
			<div class="blue-box">
			<h2 style="color:#fff;font-weight:700;">Message</h2>
				<h2 style="color:#fff;">Thoroughly verify and confirm the accuracy of all the information provided.</h2>
			</div>
		</div><br>
		<?php

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
					<h2><i class="fas fa-check"></i> Application Status</h2>
					<?php if (isset($application['Application_ID'])) { ?>
						<?php if ($application['Application_ID']) { ?>
							<h2>Application Submitted: Yes</p>
							<?php } else { ?>
								<h2>Application Submitted: No</p>
								<?php } ?>
							<?php } else { ?>
								<h2>No application</p>
								<?php } ?>
								<div class="arrow"><i class="fas fa-arrow-right"></i></div>
				</div>
			</a>

			<a href="interview.php">
				<div class="dashboard-module <?php echo $interview['Approval'] == "Approve" ? 'approved' : ($interview['Approval'] == "Reject" ? 'rejected' : 'not-approved'); ?>">
					<h2><i class="fas fa-calendar-alt"></i> Interview Status</h2>
					<?php if (isset($application['Approval'])) { ?>
						<?php if ($application['Approval'] == "Approve") { ?>
							<h2>Interview Approved: Yes</p>
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
					<h2><i class="fas fa-check-double"></i> Confirmation Status</h2>
					<?php if (isset($application['Confirmation'])) { ?>
						<?php if ($application['Confirmation'] == "Confirm") { ?>
							<h2>Confirmation Confirmed: Yes</p>
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
		<br>
		<h2 style="text-align:center;font-weight:700;">Application </h2><br>
		<?php

$existing_student_query = "SELECT * FROM applicant WHERE Student_ID='$user_student'";
$existing_student_result = $conn->query($existing_student_query);
if ($existing_student_result->num_rows > 0) {
	echo "<p style='display:flex; justify-content:center; align-items:center; height:100%;'>You have already applied!.</p>";
	exit;
}

		// Query to check the status of the club application
		$sql = "SELECT * FROM club WHERE Club_ID = $faculty_student";
		$result = mysqli_query($conn, $sql);

		if ($result && mysqli_num_rows($result) > 0) {
			// Get the value of the is_open column
			$row = mysqli_fetch_assoc($result);
			$status_application = $row['Status_Application'];

			if ($status_application == 1) {
		?>

				<div class="form-container">
					<form name="frmContact" id="frmContact" method="POST" action="" enctype="multipart/form-data">
						<div class="input-row">
							<label>Name</label> <span id="userName-info" class="info"></span>
							<h2><?php echo ucwords($row1['Student_Name']); ?></h2>
							<input type="hidden" name="name" id="userName" value="<?php echo $row1['Student_Name']; ?>">
						</div>
						<div class="input-row">
							<label>Matric Number</label> <span id="matricNumber-info" class="info"></span>
							<h2 style="text-align:left;"><?php echo ucwords($row1['Student_ID']); ?></h2>
							<input type="hidden" name="matric_number" id="matricNumber" value="<?php echo $row1['Student_ID']; ?>">
						</div>
						<div class="input-row">
							<label>Faculty</label><span id="faculty-info" class="info"></span>
							<h2 style="text-align:left;"><?php echo ucwords($row2['Faculty_Name']); ?></h2>
							<input type="hidden" name="faculty" value="<?php echo $row1['Faculty_ID']; ?>">
						</div>
						<div class="input-row">
							<label>Course</label><span id="course-info" class="info"></span>
							<input type="text" class="input-field" name="course" id="course" placeholder="Enter your course" required="" autofocus="" />
						</div>
						<div class="input-row">
							<label>Year</label><span id="year-info" class="info"></span>
							<select name="year" required>
								<option value="">Select Year</option>
								<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
							</select>
						</div>
						<div class="input-row">
							<label>Semester</label><span id="sem-info" class="info"></span>
							<select name="semester" required>
								<option value="">Select Semester</option>
								<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
							</select>
						</div>
						<div class="input-row">
							<label>Phone Number</label> <span id="phoneNumber-info" class="info"></span>
							<input type="text" class="input-field" name="phone_number" id="phoneNumber" placeholder="Enter your phone number" required="" autofocus="" />
						</div>
						<div class="input-row">
							<label>Email</label> <span id="email-info" class="info"></span>
							<input type="text" class="input-field" name="email" id="email" placeholder="Enter your email" required="" autofocus="" />
						</div>
						<div class="input-row">
							<label>Resume</label> <span id="resume-info" class="info"></span>
							<input type="file" class="input-field" name="resume" required />
						</div>
						<div>
							<input type="submit" name="submit" class="btn-submit" value="Submit" />
						</div>
					</form>
			<br>
				</div><br><br>
		<?php
			} else {
				echo '<br>';
				// Show the message that the club application is closed
				echo "<p style='display:flex; justify-content:center; align-items:center; height:100%;'>Club Application is Closed.</p>";
				echo "<p style='display:flex; justify-content:center; align-items:center; height:100%;'>Sorry, the club application is currently closed.</p>";
			}
		} else {
			// Show the message that the club application status is not available
			echo '<h2>Club Application Status Unavailable</h2>';
			echo '<h2>Sorry, the status of the club application is not available.</p>';
		}
		?>
</body>

</html>
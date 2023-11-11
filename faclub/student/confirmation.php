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

$query1 = mysqli_query($conn, "SELECT s.*, c.Club_ID as Club_ID, c.Club_Name as Club_Name ,c.Status_Application as Status_Application FROM student s LEFT JOIN club c ON s.Faculty_ID = c.Faculty_ID WHERE s.Student_ID = '$user_student'");
$row1 = mysqli_fetch_array($query1);

$query = "SELECT Status_Application FROM club WHERE Club_ID = $faculty_student";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

$status = $row['Status_Application'];

if ($status == 1) {
  $status = "Open";
} else {
  $status = "Close";
}

// Process form data when submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

	$existing_student_query = "SELECT * FROM applicant WHERE Student_ID='$user_student'";
$existing_student_result = $conn->query($existing_student_query);
if ($existing_student_result->num_rows > 0) {
    // Student has already applied, show message and exit
    echo "You have already applied!";
    exit;
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
				<h2 style="color:#fff;">Keep update the club's profile regularly.</h2>
        </div>
    </div><br>
	<?php

    
    
  // Check for connection errors
  if ($conn->connect_error) {
    die("Connection failed: An error occurred.");
}


// Define the SQL query to retrieve data from the database
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
$interview_query = "SELECT p.Applicant_Name as Applicant_Name, a.Confirmation as Confirmation, a.Whatsapp_Link as Whatsapp_Link 
FROM application a JOIN applicant p ON a.Applicant_ID = p.Applicant_ID WHERE a.Confirmation IS NOT NULL AND a.Student_ID = ?";

$interview_stmt = $conn->prepare($interview_query);
$interview_stmt->bind_param("s", $user_student);
$interview_stmt->execute();
$interview_result = $interview_stmt->get_result();

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
  <h2 style="text-align:center;font-weight:700;">Confirmation Table</h2>
    <br><br>
    <?php
    // Check if there are any rows returned by the query
    if ($interview_result->num_rows > 0) {
        // Check if the Approval column has any data
        $has_approval_data = false;
        while ($row = $interview_result->fetch_assoc()) {
            if ($row["Confirmation"] != 'Pending') {
                $has_approval_data = true;
                break;
            }
        }

        // If the Approval column has data, display the table
        if ($has_approval_data) {
            ?>
            <div class="announcement-list">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Whatsapp Group Link</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    // Initialize the row count to 1
                    $row_num = 1;

                    // Loop through each row of data and display it in the table
                    $interview_result->data_seek(0); // Reset the result pointer
                    while ($row = $interview_result->fetch_assoc()) {
                        if ($row["Confirmation"] != 'Pending') {
                            echo "<tr>";
                            echo "<td>" . $row_num . "</td>";
                            echo "<td>" . $row["Applicant_Name"] . "</td>";
                            if ($row["Confirmation"] == "Confirm") {
                                echo "<td style='color:green'>" . $row["Confirmation"] . "</td>";
                            } elseif ($row["Confirmation"] == "Reject") {
                                echo "<td style='color:red'>" . $row["Confirmation"] . "</td>";
                            } else {
                                echo "<td>" . $row["Confirmation"] . "</td>";
                            }
                            echo "<td><a href='" . $row["Whatsapp_Link"] . "' target='_blank'>" . $row["Whatsapp_Link"] . "</a></td>";
                            echo "</tr>";

                            // Increment the row count
                            $row_num++;
                        }
                    }
                    ?>
                </tbody>

            </table>
            </div>
            <?php
        } else {
            // If the Approval column does not have data, display a centered "nothing" message
            echo "<p style='display:flex; justify-content:center; align-items:center; height:100%;'>Rest assured. Your application is still being reviewed by the club.</p>";
        }
    } else {
        // If there are no rows, display a centered "nothing" message
        echo "<p style='display:flex; justify-content:center; align-items:center; height:100%;'>Rest assured. Your application is still being reviewed by the club.</p>";
    }
?>

		
		<script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript">
			
		</script>
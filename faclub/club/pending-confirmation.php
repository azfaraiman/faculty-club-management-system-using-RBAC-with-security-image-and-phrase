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

$conn = new mysqli("localhost", "root", "", "faclubdb");

$query1 = $conn->query("SELECT Club_ID, Club_Name FROM club c WHERE Club_ID = '$faculty_club'");
$row1 = $query1->fetch_assoc();

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
        <h1 style="font-size:30px">PENDING CONFIRMATION </h1>
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
if (isset($_POST['update'])) {
    // get data from form
    $whatsapp = $conn->real_escape_string($_POST['whatsapp']);
    $applicant_id = $conn->real_escape_string($_POST['applicant_id']);
    $student_id = $conn->real_escape_string($_POST['student_id']);
    $confirmation = $conn->real_escape_string($_POST['confirmation']);

    // check if data exists in the database
    $query = "SELECT * FROM application WHERE Applicant_ID = ? AND Club_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $applicant_id, $faculty_club);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // if data exists, update it
    if ($result->num_rows > 0) {
        $update_query = "UPDATE application SET Confirmation=?, Whatsapp_Link=? WHERE Applicant_ID=? AND Club_ID=?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssii", $confirmation, $whatsapp, $applicant_id, $faculty_club);
        if ($update_stmt->execute()) {
            $logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Applicant_ID, Date, Time) VALUES ('$user_club', 'Update Confirmation', '$applicant_id', CURDATE(), CURTIME())";
				mysqli_query($conn, $logQuery);
            echo "<script>alert('Updated successfully!');</script>";
        } else {
            echo "<script>alert('Failed to update data.');</script>";
        }
    } else {
        // insert data
        $insert_query = "INSERT INTO application (Student_ID, Confirmation, Whatsapp_Link, Applicant_ID, Club_ID) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("sssii", $student_id, $confirmation, $whatsapp, $applicant_id, $faculty_club);
        if ($insert_stmt->execute()) {
            $logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Applicant_ID, Date, Time) VALUES ('$user_club', 'Update Confirmation', '$applicant_id', CURDATE(), CURTIME())";
            mysqli_query($conn, $logQuery);
            echo "<script>alert('Inserted successfully!');</script>";
        } else {
            echo "<script>alert('Failed to insert data.');</script>";
        }
    }
}

if (isset($_POST['delete'])) {
    $delete_id = $conn->real_escape_string($_POST['delete']);

    // Delete the row from the "application" table
    $delete_application_query = "DELETE FROM application WHERE Applicant_ID = ? AND Club_ID = ?";
    $delete_application_stmt = $conn->prepare($delete_application_query);
    $delete_application_stmt->bind_param("ii", $delete_id, $faculty_club);

    // Delete the row from the "applicant" table
    $delete_applicant_query = "DELETE FROM applicant WHERE Applicant_ID = ?";
    $delete_applicant_stmt = $conn->prepare($delete_applicant_query);
    $delete_applicant_stmt->bind_param("i", $delete_id);

    // Execute the delete queries
    if ($delete_application_stmt->execute() && $delete_applicant_stmt->execute()) {
        $logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Applicant_ID, Date, Time) VALUES ('$user_club', 'Delete Application', '$delete_id', CURDATE(), CURTIME())";
        mysqli_query($conn, $logQuery);
        echo "<script>alert('Deleted successfully!');</script>";
    } else {
        echo "<script>alert('Failed to delete data.');</script>";
    }
}


        // execute query
        $sql = "SELECT p.Applicant_ID as applicant_id, p.Student_ID as student_id, p.Applicant_Name as applicant_name, a.Confirmation as confirmation, a.Whatsapp_Link as whatsapp
FROM application a JOIN applicant p ON a.Applicant_ID = p.Applicant_ID WHERE p.Club_ID=? AND a.Approval = 'Approve'";
       $stmt = $conn->prepare($sql);
       $stmt->bind_param("i", $faculty_club);
        $stmt->execute();
        $result = $stmt->get_result();

        echo '<div class="announcement-list">';
        echo '<table>';
        echo '<tr><th>No</th><th>Student ID</th><th>Name</th><th>Status</th><th>Confirm</th><th>Reject</th><th>Group Whatsapp Link</th><th>Action</th></tr>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<form method="post" action="" enctype="multipart/form-data">';
            echo '<tr>';
            echo '<td>' . $row['applicant_id'] . '</td>';
            echo '<td>' . $row['student_id'] . '</td>';
            echo '<td>' . $row['applicant_name'] . '</td>';
            echo '<td ';
            if ($row['confirmation'] == "Confirm") {
                echo "style='color:green'";
            } else if ($row['confirmation'] == "Reject") {
                echo "style='color:red'";
            }
            echo '>';
            if ($row['confirmation'] == "Confirm") {
                echo 'Confirm';
            } else if ($row['confirmation'] == "Reject") {
                echo 'Reject';
            } else {
                echo 'Pending';
            }
            echo '</td>';
            
            echo '<td>';
            echo '<input type="radio" name="confirmation" value="Confirm" '.($row['confirmation'] == "Confirm" ? 'checked' : '').'><br>';
            echo '</td>'; 
            echo '<td>';            
            echo '<input type="radio" name="confirmation" value="Reject" '.($row['confirmation'] == "Reject" ? 'checked' : '').'>';
            echo '</td>';       
            echo '<td><input type="text" name="whatsapp" value="' . $row['whatsapp'] . '"></td>';
            echo '<input type="hidden" name="applicant_id" value="' . $row['applicant_id'] . '">';
            echo '<input type="hidden" name="student_id" value="' . $row['student_id'] . '">';
            echo '<input type="hidden" name="club_id" value="' . $faculty_club . '">';
            echo '<td><button type="submit" class="btn-submit-table" name="update">Update</button> &nbsp <button type="submit" class="btn-delete-table" name="delete" value="' . $row['applicant_id'] . '">Delete</button></td>';
            echo '</tr>';
            echo '</form>';
        }
        echo '</table>';
        echo'</div>';
        // close connection
        mysqli_close($conn);
        ?>

        <script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript">

        </script>
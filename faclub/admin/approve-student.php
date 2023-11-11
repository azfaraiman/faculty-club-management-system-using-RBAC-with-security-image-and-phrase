<?php
require_once 'connection.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_admin = $_SESSION['username'];
// Check if the form is submitted and the student ID is provided
if (isset($_POST['approve']) && isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];
    
    require_once 'connection.php';

    // Check for connection error
    if ($conn->connect_error) {
        die("Connection failed: An error occurred.");
    }
    
    // Update the student's status to "Approved"
    $stmt = $conn->prepare("UPDATE student SET Admin_ID = ?, Status = 'Approved' WHERE Student_ID = ?");
    $stmt->bind_param("is", $user_admin, $student_id);
    if ($stmt->execute()) {
        $logQuery = "INSERT INTO log_table (Admin_ID, Log_Type, Student_ID, Date, Time) VALUES ('$user_admin', 'Approve Student', '$student_id', CURDATE(), CURTIME())";
                                mysqli_query($conn, $logQuery);
        echo '<script>alert("Student registration approved successfully.");window.location.href = "edit-student.php";</script>';
    } else {
        echo '<script>alert("Student registration failed.");window.location.href = "edit-student.php";</script>';
    }
} else {
    // Redirect back to the admin approval page if the form is not submitted correctly
    header("Location: edit-student.php");
    exit();
}
?>

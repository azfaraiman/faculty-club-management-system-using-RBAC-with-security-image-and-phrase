<?php
require_once 'connection.php';

// check connection
if (!$conn) {
    die("Connection failed: An error occurred.");
}


// get ID of applicant to download resume for
$id = $_GET['id'];

// prepare and execute query to get resume data
$sql = "SELECT Applicant_Resume,Student_ID FROM applicant WHERE Student_ID=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// get resume data and send to browser for download
$row = mysqli_fetch_assoc($result);
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $row['Student_ID'] . '_resume.pdf"');
echo $row['Applicant_Resume'];

// close connection
mysqli_close($conn);
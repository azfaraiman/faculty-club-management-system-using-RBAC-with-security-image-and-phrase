<?php
require_once 'connection.php';
session_start();
$user_admin = $_SESSION['username'];
// Destroy session data
$logQuery = "INSERT INTO log_table (Admin_ID, Log_Type, Date, Time) VALUES ('$user_admin', 'Logout', CURDATE(), CURTIME())";
						mysqli_query($conn, $logQuery);
session_destroy();
// Redirect user to login page
header("Location: /faclub/login_admin.php?message=Logout successfully.");
exit();
?>

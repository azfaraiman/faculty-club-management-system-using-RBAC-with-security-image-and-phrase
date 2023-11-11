<?php
require_once 'connection.php';
session_start();
$user_club = $_SESSION['username_club'];
// Destroy session data
$logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$user_club', 'Logout', CURDATE(), CURTIME())";
						mysqli_query($conn, $logQuery);
session_destroy();

// Redirect user to login page
header("Location: /faclub/login.php?message=Logout successfully.");
exit();
?>

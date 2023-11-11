<?php
require_once 'connection.php';
session_start();
$user_student = $_SESSION['username_student'];
// Destroy session data
$logQuery = "INSERT INTO log_table (Student_ID, Log_Type, Date, Time) VALUES ('$user_student', 'Logout', CURDATE(), CURTIME())";
						mysqli_query($conn, $logQuery);
session_destroy();


header("Location: /faclub/login.php?message=Logout successfully.");
exit();
?>

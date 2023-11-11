<?php
// Import PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

session_start();

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Retrieve the entered email address
    $email = $_POST['email'];

    // Check if the email exists in the student table
    require_once 'connection.php';
    $stmt = $conn->prepare("SELECT * FROM student WHERE Student_Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $num_rows = $result->num_rows;

    if ($num_rows > 0) {
        // Generate a random OTP
        $otp = mt_rand(100000, 999999);

        // Store the OTP and email in session variables
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;

        // Send the OTP to the user's email address
        $mail = new PHPMailer(true);

        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = "azfaraiman02@gmail.com";
            $mail->Password = "ktqzjzlzprbyeqkf";
            $mail->Port = 587;
            $mail->SMTPSecure = 'tls';

            // Email content
            $mail->setFrom('azfaraiman02@gmail.com', 'FaClub UTHM');
            $mail->addAddress($email);
            $mail->Subject = 'FaClub UTHM Password Reset OTP - Action Required';
            $mail->Body = 'Dear User,

We have received a request to reset your password for your account associated with the provided email address. To proceed with the password reset, please use the One-Time Password (OTP) below:

OTP: ' . $otp . '

Please note that this OTP is valid for a limited time and should be kept confidential. Do not share it with anyone.If you didn\'t initiate this password reset, please disregard this email. Your account is safe, and no further action is required.

Thank you for your cooperation.

Best regards,
Wan Azfar Aiman Bin Wan Azmi
Faclub UTHM';

            // Send the email
            $mail->send();

            // Display a message to the user
            echo '<script>alert("An OTP has been sent to your email address. Please check your inbox.");</script>';

            // Redirect the user to the OTP verification page
            header("Location: reset-password-verify.php");
            exit();
        } catch (Exception $e) {
            echo "Email could not be sent.";
        }
    } else {
        echo '<script>alert("Invalid email address. Please try again.");window.location.href = "reset-password.php";</script>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" type="text/css" href="style-login.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div id="wrapper">
    <h2>EMAIL VERIFICATION</h2>
    <form method="POST" action="">
    <div class="blue-box">
		<h4 style="color:#fff;">A verification code will be sent to the email you provide.</h4>
        </div><br><br>

        <label>Email:</label><br>
        <input type="text" placeholder="Enter email" name="email" required="" autofocus=""><br><br>
        <input type="submit"  class="btn-submit" name="submit" value="Send">
    </form>
    <br><br>
</body>
</html>

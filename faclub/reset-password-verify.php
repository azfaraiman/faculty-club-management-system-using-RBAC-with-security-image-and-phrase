<?php
require_once 'connection.php';
session_start();

// Check if OTP and email are set in session
if (!isset($_SESSION['otp']) || !isset($_SESSION['email'])) {
    // If not, redirect the user back to the reset password page
    header("Location: reset-password.php");
    exit();
}

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Retrieve the entered OTP
    $enteredOTP = $_POST['otp'];

    // Compare the entered OTP with the one stored in session
    if ($enteredOTP == $_SESSION['otp']) {
        // OTP is valid, proceed with password update

        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if ($newPassword !== $confirmPassword) {
            echo '<script>alert("Passwords do not match.");window.location.href = "reset-password.php";</script>';
            exit;
        }
        elseif (!validatePassword($newPassword)) {
            echo '<script>alert("Password is invalid. It should have at least one symbol, one number, one capital letter, and be more than 12 characters long. Please follow the password meter.");</script>';
        } else {
        $table = ''; // Define the table name based on the user role
        $username_field = ''; // Define the username field based on the user role
        $password_field = ''; // Define the password field based on the user role
        $faculty_field = '';

        if (isset($_SESSION['role']) && $_SESSION['role'] == '2') {
            $table = 'student';
            $username_field = 'Student_ID';
            $password_field = 'Student_Password';
            $faculty_field = 'Faculty_ID';
        } elseif (isset($_SESSION['role']) && $_SESSION['role'] == '3') {
            $table = 'club';
            $username_field = 'Club_ID';
            $password_field = 'Club_Password';
            $faculty_field = 'Faculty_ID';
        } else {
            echo '<script>alert("Invalid user role.");window.location.href = "login.php";</script>';
            exit;
        }

        // Update the password in the database
        $username = '';
        $faculty = '';

        if ($_SESSION['role'] == '2') {
            $username = mysqli_real_escape_string($conn, $_SESSION['username_student']);
            $faculty = mysqli_real_escape_string($conn, $_SESSION['faculty_student']);
            unset($_SESSION['login_attempts'][$username]);
        } elseif ($_SESSION['role'] == '3') {
            $username = mysqli_real_escape_string($conn, $_SESSION['username_club']);
            $faculty = mysqli_real_escape_string($conn, $_SESSION['faculty_club']);
            unset($_SESSION['login_attempts'][$username]);
        } else {
            echo '<script>alert("Invalid user role.");window.location.href = "login2.php";</script>';
            exit;
        }

        $stmt = $conn->prepare("SELECT $password_field FROM $table WHERE $username_field = ? AND $faculty_field = ?");
        $stmt->bind_param("ss", $username, $faculty);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $storedHashedPassword = $row[$password_field];
        if (password_verify($newPassword, $storedHashedPassword)) {
            echo '<script>alert("New password cannot be the same as the old password.");window.location.href = "reset-password.php";</script>';
            exit;
        }

        $hashedPassword = hash('sha256', $newPassword);
        $_SESSION['reset_password_hash'] = $hashedPassword;

        $stmt = $conn->prepare("UPDATE $table SET $password_field = ? WHERE $username_field = ? AND $faculty_field = ?");
        $stmt->bind_param("sss", $hashedPassword, $username, $faculty);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo '<script>alert("Password reset successfully. Please log in with your new password.");window.location.href = "login.php";</script>';
            exit;
        } else {
            echo '<script>alert("Failed to reset the password. Please try again later.");window.location.href = "reset-password.php";</script>';
            exit;
        }
    }
    } else {
        // Invalid OTP, display an error message
        echo '<script>alert("Invalid OTP. Please try again.");window.location.href = "reset-password.php";</script>';
        exit;
    }
}

function validatePassword($password)
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&^#])[A-Za-z\d@$!%*?&^#]{8,}$/', $password);
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - Verify OTP</title>
    <link rel="stylesheet" type="text/css" href="style-login.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div id="wrapper">
    <form action="" method="POST">
        <h2>RESET PASSWORD</h2><br><br>
        <div class="blue-box">
            <h4 style="color:#fff;">Please enter the 6-digit verification code that was sent to your email.</h4>
        </div><br>
        <label>OTP:</label><br>
        <input type="text" name="otp" required><br><br><br>
        <div class="blue-box">
            <h4 style="color:#fff;">Please note that your new password must be different from your previous password for security reasons.</h4>
        </div><br>
        <label>New Password:</label><span class="tooltip">?</span>
        <div id="password-strength-container">
            <label></label>
            <meter id="password-strength" min="0" max="100" value="0"></meter>
        </div>
        <div class="password-wrapper">
            <input type="password" name="new_password" id="passwordInput" placeholder="Enter password" required="">
            <span class="toggle-password">
                <i class="fas fa-eye"></i>
            </span>
        </div>
        <label>Confirm Password:</label><br>
        <div class="password-wrapper">
            <input type="password" name="confirm_password" id="passwordInput2" placeholder="Enter password" required="">
            <span class="toggle-password2">
                <i class="fas fa-eye"></i>
            </span>
        </div>
        <div id="password-validation" style="color: red;"></div>
        <input type="submit" name="submit" class="btn-submit" value="Reset" />
    </form><br><br>
</div>
</body>
<script>
    const passwordInput = document.querySelector('input[name="new_password"]');
    const password2Input = document.querySelector('input[name="confirm_password"]');

    function validatePasswords() {
        if (passwordInput.value !== password2Input.value) {
            password2Input.setCustomValidity("Passwords do not match");
        } else {
            password2Input.setCustomValidity("");
        }
    }

    passwordInput.addEventListener("input", validatePasswords);
    password2Input.addEventListener("input", validatePasswords);

    const passwordStrengthMeter = document.getElementById('password-strength');

passwordInput.addEventListener('input', () => {
    const password = passwordInput.value;
    const passwordStrength = calculatePasswordStrength(password);
    passwordStrengthMeter.value = passwordStrength;
});


function calculatePasswordStrength(password) {
  let passwordStrength = 0;

  // Check length
  if (password.length >= 8) {
    passwordStrength += 25;
  }

  // Check for uppercase letters
  if (/[A-Z]/.test(password)) {
    passwordStrength += 25;
  }

  // Check for lowercase letters
  if (/[a-z]/.test(password)) {
    passwordStrength += 25;
  }

  // Check for numbers
  if (/\d/.test(password)) {
    passwordStrength += 25;
  }

  // Check for symbol
  if (/[@$!%*?&^#]/.test(password)) {
    passwordStrength += 25;
  }

  return passwordStrength;
}

    var togglePassword = document.querySelector(".toggle-password");
        togglePassword.addEventListener("click", function () {
            var passwordInput = document.querySelector("#passwordInput");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
            } else {
                passwordInput.type = "password";
            }
        });

        var togglePassword2 = document.querySelector(".toggle-password2");
        togglePassword2.addEventListener("click", function () {
            var password2Input = document.querySelector("#passwordInput2");
            if (password2Input.type === "password") {
                password2Input.type = "text";
            } else {
                password2Input.type = "password";
            }
        });

    var passwordValidation = document.getElementById("password-validation");

        passwordInput.addEventListener("input", function () {
            var password = passwordInput.value;
            var valid = validatePassword(password);

            if (valid) {
                passwordValidation.innerText = "";
            } else {
                passwordValidation.innerText = "Password is invalid. It should have at least one symbol, one number, be more than 8 characters long, and contain at least one uppercase letter.";
                passwordValidation.style.fontSize = "13px";
            }
        });
        password2Input.addEventListener("input", function () {
    var password = passwordInput.value;
    var confirmPassword = password2Input.value;
    var valid = validatePassword(password);

    if (valid && password === confirmPassword) {
        passwordValidation.innerText = "";
    } else {
        passwordValidation.innerText = "Password is invalid or passwords do not match. It should have at least one symbol, one number, be more than 8 characters long, and contain at least one uppercase letter.";
        passwordValidation.style.fontSize = "13px";
    }
});
function validatePassword($password) {
            return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&^#])[A-Za-z\d@$!%*?&^#]{8,}$/.test($password);
        }
</script>
</html>

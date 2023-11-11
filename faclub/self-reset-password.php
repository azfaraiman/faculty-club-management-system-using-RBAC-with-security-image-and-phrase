<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="style-login.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<?php
require_once 'connection.php';
session_start();


if (isset($_POST['reset'])) {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        echo '<script>alert("Passwords do not match.");window.location.href = "reset-password.php";</script>';
        exit;
    }

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

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
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

    if ($_SESSION['role'] == '2') {
        unset($_SESSION['username_student']);
        unset($_SESSION['faculty_student']);
    } elseif ($_SESSION['role'] == '3') {
        unset($_SESSION['username_club']);
        unset($_SESSION['faculty_club']);
    }
    unset($_SESSION['login_attempts']);
}
?>
<body>
    <div id="wrapper">
        <form action="" method="POST">
            <h2>RESET PASSWORD</h2><br><br>
            <label>New Password:</label><span class="tooltip">?</span>
            <div id="password-strength-container">
                <label></label>
                <meter id="password-strength" min="0" max="100" value="0"></meter>
            </div>
            <input type="password" name="new_password" required autofocus=""><br>
            <label>Confirm Password:</label><br>
            <input type="password" name="confirm_password" required=""><br>
            <input type="submit" name="reset" class="btn-submit" value="Reset" />
        </form>
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
        } else if (password.length >= 6) {
            passwordStrength += 10;
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

        return passwordStrength;
    }
</script>

<script>
    const togglePassword = document.querySelector("#togglePassword");
    const password = document.querySelector("#password-input");

    togglePassword.addEventListener("click", function() {
        // toggle the type attribute
        const type = password.getAttribute("type") === "password" ? "text" : "password";
        password.setAttribute("type", type);

        // toggle the icon class
        this.classList.toggle("bi-eye");
        this.classList.toggle("bi-eye-slash");
    });

    // prevent form submit
    const form = document.querySelector("form");
    form.addEventListener('submit', function(e) {
        e.preventDefault();
    });
</script>

</html>
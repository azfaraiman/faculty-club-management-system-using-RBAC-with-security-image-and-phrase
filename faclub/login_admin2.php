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
    session_start();
    $conn = mysqli_connect('localhost', 'root', '', 'faclubdb');
    if (!$conn) {
        die("Connection failed: An error occurred.");
    }

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $username = mysqli_real_escape_string($conn, $_GET['username']);
        $role = 1;

        if (empty($username)) {
            echo '<script>alert("Your UserName is not Found!");window.location.href = "login_admin.php";</script>';
            exit;
        } else if ($role == '1') {
            $query = "SELECT Security_Image, Security_Phrase, Admin_Password FROM admin WHERE Admin_ID=? AND Role_ID=?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ss", $username, $role);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $security_image, $security_phrase, $password);
            mysqli_stmt_fetch($stmt);

            if (!$security_image || !$security_phrase) {
                echo '<script>alert("No security image or phrase found for this user!");window.location.href = "login_admin.php";</script>';
                exit;
            }
        } else {
            echo '<script>alert("Your UserName is not Found!");window.location.href = "login_admin.php";</script>';
            exit;
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $username = mysqli_real_escape_string($conn, $_GET['username']);
        $role = 1;
        if ($role == '1') {
            $table = 'admin'; // set table to 'admin' if user is an admin
            $username_field = 'Admin_ID'; // set the username field name for the admin table
            $password_field = 'Admin_Password'; // set the password field name for the admin table
        } else {
            echo '<script>alert("Your UserName is not Found!");window.location.href = "login_admin.php";</script>';
            exit;
        }

        $query = "SELECT * FROM $table WHERE $username_field = '$username'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);

        $password_from_database = $row[$password_field];

        if ($password == $password_from_database) { // check if the password is correct
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            if ($_SESSION['role'] == '1') {
                // show the admin page
                header('Location: admin/index-admin.php');
            } 
        } else {

            echo '<script>alert("Incorrect password! Please try again.");window.location.href = "login_admin.php";</script>';
            exit;
        }
    }

    ?>

    <div id="wrapper">
        <form action="" method="POST">
            <img src="img/UTHM.png" alt="My Image">
            <h2> FACLUB@UTHM </h2>
            <h3>UTHM Faculty Club Management System</h3><br>
            <div class="blue-box">
		<h4 style="color:#fff;">Important: Before entering your password, verify that the displayed image and phrase match your registered credentials to ensure maximum security!</h4>
        </div><br><br>
            <h4> Security Image</h4>
            <img style="width:120px;height:120px; margin: auto; display: block; border: 2px solid #000;" src="data:image/jpg;base64,<?php echo base64_encode($security_image); ?> "><br>
            <h4> Security Phrase</h4>
            <h5><?php echo ($security_phrase); ?></h5>
            <br>
            <h4 style="text-align: left;"><input type="checkbox" id="checksecurity" onclick="ShowHideDiv(this)" value="checkbox_value">This is my security image and phrase</h4>
            <div id="password" style="display: none">
                <label>Password:</label><br>
                <div class="password-wrapper">
        <input type="password" name="password" id="passwordInput" required="" autofocus="">
        <span class="toggle-password">
            <i class="fas fa-eye"></i>
        </span>
    </div>
                <input type="hidden" name="username" value="<?php echo $username; ?>">
                <input type="hidden" name="role" value="<?php echo $role; ?>">
                <input type="submit" name="login" class="btn-submit" value="Login" />
            </div>
        </form>
        <br><br>
    </div>


    <script type="text/javascript">
        function ShowHideDiv(checksecurity) {
            var password = document.getElementById("password");
            password.style.display = checksecurity.checked ? "block" : "none";
        }
    </script>
    <script>
        const resetPasswordCheckbox = document.getElementById('resetpassword');

        resetPasswordCheckbox.addEventListener('click', () => {
            if (resetPasswordCheckbox.checked) {
                window.location.href = 'self-reset-password.php';
            }
        });
    </script>

<script>
    var passwordInput = document.getElementById("passwordInput");
    var togglePassword = document.querySelector(".toggle-password");

    togglePassword.addEventListener("click", function () {
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
        } else {
            passwordInput.type = "password";
        }
    });
</script>
</body>

</html>
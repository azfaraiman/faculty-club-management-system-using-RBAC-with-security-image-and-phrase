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
    session_set_cookie_params([
        'lifetime' => 1800, // Set the session timeout in seconds (e.g., 3600 = 1 hour)
        'path' => '/',
        'domain' => '',
        'secure' => true, // Only transmit the cookie over HTTPS
        'httponly' => true // Restrict cookie access to HTTP only
    ]);

    session_start();
    session_regenerate_id(true);

    if (isset($_POST['login'])) {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $faculty = mysqli_real_escape_string($conn, $_POST['faculty']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);

        if (empty($username) || empty($faculty) || empty($role)) {
            echo '<script>alert("Your UserName or Role is not Found!");window.location.href = "login2.php";</script>';
            exit;
        } else {
            if ($role == '2') {
                $table = 'student'; // set table to 'student' if user is a student
                $username_field = 'Student_ID'; // set the username field name for the student table
                $password_field = 'Student_Password'; // set the password field name for the student table
            } else if ($role == '3') {
                $table = 'club'; // set table to 'club' if user is a club member
                $username_field = 'Club_ID'; // set the username field name for the club table
                $password_field = 'Club_Password'; // set the password field name for the club table
            } else {
                echo '<script>alert("Your UserName or Role is not Found!");window.location.href = "login2.php";</script>';
                exit;
            }

            // Checking Login Detail
            $stmt = $conn->prepare("SELECT * FROM $table WHERE $username_field=? AND Faculty_ID=? AND Role_ID=?");
            $stmt->bind_param("sii", $username, $faculty, $role);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $count = $result->num_rows;


            if ($count == 0) {
                echo '<script>alert("No matching login details found.");window.location.href = "login2.php";</script>';
                exit();
            } else if ($count == 1) {
                if (!isset($_SESSION['login_attempts'])) {
                    $_SESSION['login_attempts'] = array();
                }

                // Check if the login attempts counter for the user exists in the session
                if (!isset($_SESSION['login_attempts'][$username])) {
                    // If not, initialize it to 0
                    $_SESSION['login_attempts'][$username] = 0;
                }

                // Increment the login attempts counter for the user
                $_SESSION['login_attempts'][$username]++;

                if ($_SESSION['login_attempts'][$username] >= 3) {
                    echo '<script>alert("You have exceeded the maximum number of login attempts. Please reset your password.");window.location.href = "reset-password.php";</script>';
                    exit();
                }

                // Verify the entered password with the stored hashed password
                $enteredPassword = mysqli_real_escape_string($conn, $_POST['password']);
                $storedHashedPassword = $row[$password_field]; // Assuming the password field name is correctly set

                if (hash('sha256', $enteredPassword) !== $storedHashedPassword) {
                    echo '<script>alert("Invalid password.");window.location.href = "login2.php";</script>';
                    exit();
                }

                // Password verification successful, clear login attempts and proceed with login
                unset($_SESSION['login_attempts'][$username]);

                if ($role == '2') {
                    $logQuery = "INSERT INTO log_table (Student_ID, Log_Type, Date, Time) VALUES ('$username', 'Login', CURDATE(), CURTIME())";
                } else if ($role == '3') {
                    $logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$username', 'Login', CURDATE(), CURTIME())";
                } else {
                    echo '<script>alert("Invalid role.");window.location.href = "login2.php";</script>';
                    exit();
                }
                mysqli_query($conn, $logQuery);
                // Step 4: Set session variables for security image and phrase
                $_SESSION['security_image'] = $row['Security_Image'];
                $_SESSION['security_phrase'] = $row['Security_Phrase'];

                // Step 5: Redirect the user to the appropriate dashboard based on their role
                if ($role == '2') {
                    header("Location: student/index.php");
                } else if ($role == '3') {
                    header("Location: club/index-club.php");
                } else {
                    echo '<script>alert("Invalid role.");window.location.href = "login2.php";</script>';
                    exit();
                }
            }
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
            <h4>Security Image</h4>
            <?php
            if ($_SESSION['role'] == '2') { // Student
                $securityImage = isset($_SESSION['student']['security_image']) ? $_SESSION['student']['security_image'] : '';
                if (!empty($securityImage)) {
                    echo '<img style="width:120px;height:120px; margin: auto; display: block; border: 2px solid #000;" src="data:image/jpg;base64,' . base64_encode($securityImage) . '"><br>';
                }
            } elseif ($_SESSION['role'] == '3') { // Club
                $securityImage = isset($_SESSION['club']['security_image']) ? $_SESSION['club']['security_image'] : '';
                if (!empty($securityImage)) {
                    echo '<img style="width:120px;height:120px; margin: auto; display: block; border: 2px solid #000;" src="data:image/jpg;base64,' . base64_encode($securityImage) . '"><br>';
                }
            }
            ?>
            <h4>Security Phrase</h4>
            <?php
            if ($_SESSION['role'] == '2') { // Student
                $securityPhrase = isset($_SESSION['student']['security_phrase']) ? $_SESSION['student']['security_phrase'] : '';
                if (!empty($securityPhrase)) {
                    echo '<h5>' . $securityPhrase . '</h5>';
                }
            } elseif ($_SESSION['role'] == '3') { // Club
                $securityPhrase = isset($_SESSION['club']['security_phrase']) ? $_SESSION['club']['security_phrase'] : '';
                if (!empty($securityPhrase)) {
                    echo '<h5>' . $securityPhrase . '</h5>';
                }
            }
            ?>
            <br>
            <h4 style="text-align: left;"><input type="checkbox" id="checksecurity" onclick="ShowHideDiv(this)" value="checkbox_value">This is my security image and phrase</h4>
            <h4 style="text-align: left;"><input type="checkbox" id="resetpassword" value="checkbox_value">Forgot my password</h4>
            <div id="password" style="display: none">
                <label>Password:</label><br>
                <div class="password-wrapper">
                    <input type="password" name="password" id="passwordInput" placeholder="Enter your password" autofocus="" value="<?php echo htmlspecialchars($password ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="toggle-password">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div id="password-validation" style="color: red;"></div>

                <?php if ($_SESSION['role'] == '2') : ?>
                    <input type="hidden" name="username" value="<?php echo $_SESSION['username_student']; ?>">
                    <input type="hidden" name="faculty" value="<?php echo $_SESSION['faculty_student']; ?>">
                <?php elseif ($_SESSION['role'] == '3') : ?>
                    <input type="hidden" name="username" value="<?php echo $_SESSION['username_club']; ?>">
                    <input type="hidden" name="faculty" value="<?php echo $_SESSION['faculty_club']; ?>">
                <?php endif; ?>

                <input type="hidden" name="role" value="<?php echo $_SESSION['role']; ?>">
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
                window.location.href = 'reset-password.php';
            }
        });
    </script>

    <script>
        var passwordInput = document.getElementById("passwordInput");
        var passwordValidation = document.getElementById("password-validation");
        var togglePassword = document.querySelector(".toggle-password");

        passwordInput.addEventListener("input", function() {
            var password = passwordInput.value;
            var valid = validatePassword(password);

            if (valid) {
                passwordValidation.innerText = "";
            } else {
                passwordValidation.innerText = "Password is invalid. It should have at least one symbol, one number, be more than 8 characters long, and contain at least one uppercase letter.";
                passwordValidation.style.fontSize = "13px";
            }
        });

        togglePassword.addEventListener("click", function() {
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
            } else {
                passwordInput.type = "password";
            }
        });

        function validatePassword(password) {
            return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&^#])[A-Za-z\d@$!%*?&^#]{8,}$/.test(password);
        }

        
    </script>
</body>

</html>
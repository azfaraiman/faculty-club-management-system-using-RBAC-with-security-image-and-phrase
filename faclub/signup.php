<!DOCTYPE html>
<html>

<head>
    <title>Signup</title>
    <link rel="stylesheet" type="text/css" href="style-login.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <?php
    require_once 'connection.php';
    if (isset($_POST['register'])) {
        // Get the form data
        $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
        $name = filter_var($_POST["name"], FILTER_SANITIZE_STRING);
        $username = filter_var($_POST["username"], FILTER_SANITIZE_STRING);
        $password = $_POST["password"];
        $faculty = filter_var($_POST["faculty"], FILTER_SANITIZE_STRING);
        $security_phrase = filter_var($_POST["security_phrase"], FILTER_SANITIZE_STRING);

        // Validate the form data
        if (empty($name) || empty($email) || empty($username) || empty($password) || empty($faculty) || empty($security_phrase)) {
            echo "Please fill in all fields.";
        } elseif (!validatePassword($password)) {
            echo '<script>alert("Password is invalid. It should have at least one symbol, one number, one capital letter, and be more than 8 characters long. Please follow the password meter.");</script>';
        } else {
            // Check if image is uploaded
            if (empty($_FILES['image']['tmp_name'])) {
                echo "Please upload an image.";
            } else {
                $imageType = $_FILES['image']['type'];
                $imageSize = $_FILES['image']['size'];
                // Validate image 
                if (in_array($imageType, array('image/jpg', 'image/jpeg', 'image/png', 'image/gif')) && $imageSize < 1000000000) {
                    $image = file_get_contents($_FILES['image']['tmp_name']);
                } else {
                    echo '<script>alert("Invalid image type or size.");</script>';
                }
            }

            // Check for connection error
            if ($conn->connect_error) {
                die("Connection failed: An error occurred.");
            }
            
            // Check if the email is already in use
            $stmt = $conn->prepare("SELECT Student_ID FROM student WHERE Student_ID = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                echo '<script>alert("Matric number already in use.");window.location.href = "signup.php";</script>';
            } else {
                $hashedPassword = hash('sha256', $password);
                $stmt = $conn->prepare("INSERT INTO student (Student_Name,Student_Email, Role_ID, Student_ID, Student_Password, 
                Faculty_ID, Security_Image, Security_Phrase, Status) VALUES (?, ?, 2, ?, ?, ?, ?, ?, 'Pending')");
                $stmt->bind_param("sssssss", $name, $email, $username, $hashedPassword, $faculty, $image, $security_phrase);
                if ($stmt->execute()) {
                    echo '<script>alert("Student registered successfully. Please wait for admin approval.");window.location.href = "login.php";</script>';
                } else {
                    echo '<script>alert("Failed to register. Please register again.");window.location.href = "signup.php";</script>';
                }
            }
        }
    }

    function validatePassword($password)
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&^#])[A-Za-z\d@$!%*?&^#]{8,}$/', $password);
    }
    ?>
    <div id="wrapper">
        <form action="signup.php" method="POST" enctype="multipart/form-data">
            <img src="img/UTHM.png" alt="My Image">
            <h2>SIGN UP</h2>
            <div class="blue-box">
                <h4 style="color:#fff;">New user needs to wait for approval from admin after signup.</h4>
            </div><br>
            <label for="username">Name:</label>
            <input type="text" name="name" placeholder="Enter student name" required="" autofocus="">
            <label for="username">Matric Number:</label>
            <input type="text" name="username" placeholder="Enter student matric number" required="" autofocus="">
            <label for="email">Email:</label>
            <input type="text" name="email" placeholder="Enter email" required="" autofocus="">
            <label for="password">Password:</label>
            <span class="tooltip">?</span>
            <div id="password-strength-container">
                <label></label>
                <meter id="password-strength" min="0" max="100" value="0"></meter>
            </div>
            <div class="password-wrapper">
                <input type="password" name="password" id="passwordInput" placeholder="Enter password" required="" autofocus="">
                <span class="toggle-password">
                    <i class="fas fa-eye"></i>
                </span>
            </div>

            <label for="password2">Confirm Password:</label>
            <div class="password-wrapper">
                <input type="password" name="password2" id="passwordInput2" placeholder="Re-enter password" required="" autofocus="">
                <span class="toggle-password2">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
            <div id="password-validation" style="color: red;"></div>

            <label for="faculty">Faculty:</label>
            <select id="faculty" name="faculty" required>
                <option value="" selected disabled>Choose Faculty</option>
                <option value="1">FSKTM</option>
                <option value="2">FKAAB</option>
                <option value="3">FKEE</option>
                <option value="4">FKMP</option>
                <option value="5">FPTP</option>
                <option value="6">FPTV</option>
                <option value="7">FAST</option>
                <option value="8">FTK</option>
            </select><br><br>
            <label for="image">Security Image:</label>
            <input type="file" id="image" name="image" accept="image/jpg, image/jpeg, image/png" required><br><br>

            <label for="security_phrase">Security Phrase:</label>
            <input type="text" id="security_phrase" name="security_phrase" placeholder="Enter security phrase" required><br>

            <button type="submit" value="register" class="btn-submit" name="register">Register</button>
            <a href="login.php" class="text-button">&lt; Return to Login Page</a>
            <br><br>
        </form>
    </div>
    <script>
        const passwordInput = document.querySelector('input[name="password"]');
        const password2Input = document.querySelector('input[name="password2"]');

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

            if (password.length >= 8) {
                passwordStrength += 25;
            }
            if (/[A-Z]/.test(password)) {
                passwordStrength += 25;
            }
            if (/[a-z]/.test(password)) {
                passwordStrength += 25;
            }
            if (/\d/.test(password)) {
                passwordStrength += 25;
            }
            if (/[@$!%*?&^#]/.test(password)) {
                passwordStrength += 25;
            }

            return passwordStrength;
        }

        var togglePassword = document.querySelector(".toggle-password");
        togglePassword.addEventListener("click", function() {
            var passwordInput = document.querySelector("#passwordInput");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
            } else {
                passwordInput.type = "password";
            }
        });

        var togglePassword2 = document.querySelector(".toggle-password2");
        togglePassword2.addEventListener("click", function() {
            var passwordInput2 = document.querySelector("#passwordInput2");
            if (passwordInput2.type === "password") {
                passwordInput2.type = "text";
            } else {
                passwordInput2.type = "password";
            }
        });

        var passwordValidation = document.getElementById("password-validation");

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
        password2Input.addEventListener("input", function() {
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
</body>

</html>
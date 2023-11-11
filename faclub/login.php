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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'connection.php';

    // Check for connection errors
    if (!$conn) {
        die("Connection failed: An error occurred.");
    }

    $username = trim($_POST['username']);
    $faculty = trim($_POST['faculty']);
    $role = trim($_POST['role']);

    if (empty($username) || empty($faculty) || empty($role)) {
        echo '<script>alert("Your UserName or Role is not Found!");window.location.href = "login.php";</script>';
        exit;
    }

    // Prepare and bind the SQL statement to prevent SQL injection
    if ($role === '2') { // Student
        $stmt = $conn->prepare("SELECT * FROM student WHERE Student_ID=? AND Faculty_ID=? AND Role_ID=? AND Status='Approved'");
    } else if ($role === '3') { // Club
        $stmt = $conn->prepare("SELECT * FROM club WHERE Club_ID=? AND Faculty_ID=? AND Role_ID=? AND Status='Approved'");

    } else {
        echo '<script>alert("Invalid Role!");window.location.href = "login.php";</script>';
        exit;
    }

    $stmt->bind_param("sss", $username, $faculty, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $count = $result->num_rows;

    if ($count === 0) {
        echo '<script>alert("No matching login details found or user not approved.");window.location.href = "login.php";</script>';
        exit();
    } else if ($count === 1 && $role === '2') { // if user is a student
        // start student session and set session variables
        $_SESSION['student'] = array(
            'username' => $row['Student_ID'],
            'faculty' => $row['Faculty_ID'],
            'role' => $row['Role_ID'],
            'security_image' => $row['Security_Image'],
            'security_phrase' => $row['Security_Phrase'],
        );
        $_SESSION['username_student'] = $username;
        $_SESSION['faculty_student'] = $faculty;
        $_SESSION['role'] = $role;
        $_SESSION['security_phrase'] = $row['Security_Phrase'];
        $_SESSION['security_image'] = $row['Security_Image'];
        // redirect to login2.php
        session_regenerate_id(true);
        header("Location: login2.php");
        exit();
    } else if ($count === 1 && $role === '3') { // if user is a club
        // start club session and set session variables
        $_SESSION['club'] = array(
            'username' => $row['Club_ID'],
            'faculty' => $row['Faculty_ID'],
            'role' => $row['Role_ID'],
            'security_image' => $row['Security_Image'],
            'security_phrase' => $row['Security_Phrase'],
        );
        $_SESSION['username_club'] = $username;
        $_SESSION['faculty_club'] = $faculty;
        $_SESSION['role'] = $role;
        $_SESSION['security_phrase'] = $row['Security_Phrase'];
        $_SESSION['security_image'] = $row['Security_Image'];
        // redirect to login2.php
        session_regenerate_id(true);
        header("Location: login2.php");
        exit();
    }
}
?>


    <div id="wrapper">
        <form action="login.php" method="POST">
            <a href="login_admin.php"><img id="uthm-logo" src="img/UTHM.png" alt="uthm-logo"></a>
            <h2> FACLUB@UTHM </h2>
            <h3>UTHM Faculty Club Management System</h3><br><br>
            <div class="blue-box">
		<h4 style="color:#fff;">Please login using registered account.</h4>
        </div><br>
            <label for="username">Matric Number / Club ID:</label>
            <input type="text" id="username" name="username" placeholder="Enter matric number / club id" required="" autofocus="" value="<?php echo htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8'); ?>">
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
            </select> &nbsp&nbsp
            <label for="role"> Role:</label>
            <select id="role" name="role" required>
                <option value="" selected disabled>Choose Role</option>
                <option value="2">Student</option>
                <option value="3">Club</option>
            </select>

            <br><br>
            <input type="submit" name="login" class="btn-submit" value="Next" />
            <a href="signup.php" class="text-button">< Go to Sign Up Page</a>
        </form>
        <br><br>
    </div>

</body>

</html>

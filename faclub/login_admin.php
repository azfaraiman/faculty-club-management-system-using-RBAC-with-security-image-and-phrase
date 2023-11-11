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
    //Getting Input value
    if (isset($_POST['login'])) {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $role = 1;
        if (empty($username)) {
            echo '<script>alert("Your UserName is not Found!");window.location.href = "login_admin.php";</script>';
            exit;
        } else {
            //Checking Login Detail for Student
            $stmt = $conn->prepare("SELECT * FROM admin WHERE Admin_ID=?");
            $stmt->bind_param("i", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $count = $result->num_rows;

            if ($count == 0) {
                echo '<script>alert("No matching login details found.");window.location.href = "login_admin.php";</script>';
                exit();
            } else if ($count == 1) {
                // start session and set session variables
                $_SESSION['user'] = array(
                    'username' => $row['Admin_ID'],
                    'role' => $row['Role_ID'],
                    'security_image' => $row['Security_Image'],
                    'security_phrase' => $row['Security_Phrase'],
                );
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                $_SESSION['security_phrase'] = $row['Security_Phrase'];
                $_SESSION['security_image'] = $row['Security_Image'];
                // redirect to login2.php
                header("Location: login_admin2.php?username=" . $username);
                exit();
            } 
                
            
        }
    }


    ?>

    <div id="wrapper">
        <form action="login_admin2.php" method="GET">
        <div class="row">
        <a href="login.php"><img id="uthm-logo" src="img/UTHM.png" alt="uthm-logo"></a>
  <h3 class ="admin-login">Admin</h3>
</div>
            <h2> FACLUB@UTHM </h2>
            <h3>UTHM Faculty Club Management System</h3>
            <br><br>
            <div class="blue-box">
		<h4 style="color:#fff;">Please login using registered account.</h4>
        </div><br>
            <label for="username">Enter Admin ID</label>
            <input type="text" id="username" name="username" placeholder="Enter id" required="" autofocus="">
            <br><br>
            <input type="submit" name="login" class="btn-submit" value="Next" />
        </form>
        <br><br>
    </div>

</body>

</html>
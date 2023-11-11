<?php
require_once 'connection.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


$corepage = explode('/', $_SERVER['PHP_SELF']);
$corepage = end($corepage);
if ($corepage !== 'index-admin.php') {
    if ($corepage == $corepage) {
        $corepage = explode('.', $corepage);
        header('Location: index-admin.php?page=' . $corepage[0]);
    }
}

$user_admin = $_SESSION['username'];

$query1 = $conn->query("SELECT * FROM club");
$row1 = $query1->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
	<head>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-Z/o1zeKj/av/IJGzmqcWnwYlPmdo+Jx5B5O5GZxlNp+MY0ilLLGNGf22cezEesWXMjv2z7Dd+y5ZOAVKr5jKpA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Edit Profile</title>
	</head>
	<body>
    <div class="wrapper">
<div class="announcement-list">
  <div style="margin-top:20px;display: flex; justify-content: space-between; width: 100%" class="container header-container">
    <h1 style="font-size:30px">PENDING STUDENT REGISTRATION</h1>
  </div>
</div><br>
<div class="announcement-list">
    <div class="blue-box">
      <h2 style="color:#fff;font-weight:700;">Message</h2>
      <h2 style="color:#fff;">Keep update the club's profile regularly.</h2>
    </div>
  </div><br><br>
    <?php
    // Connect to the database
    require_once 'connection.php';

    // Check for connection error
    if ($conn->connect_error) {
        die("Connection failed: An error occurred.");
    }
    

    // Retrieve pending student registrations
    $stmt = $conn->prepare("SELECT s.*, f.Faculty_Name as Faculty_Name FROM student s JOIN faculty f ON s.Faculty_ID = f.Faculty_ID WHERE s.Status = 'Pending'");
    $stmt->execute();
    $result = $stmt->get_result();
    ?>

        <div class="announcement-list">
        <table>
            <tr>
                <th>Name</th>
                <th>Matric Number</th>
                <th>Email</th>
                <th>Faculty</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['Student_Name']; ?></td>
                    <td><?php echo $row['Student_ID']; ?></td>
                    <td><?php echo $row['Student_Email']; ?></td>
                    <td><?php echo $row['Faculty_Name']; ?></td>
                    <td>
                        <form action="approve-student.php" method="POST">
                            <input type="hidden" name="student_id" value="<?php echo $row['Student_ID']; ?>">
                            <button type="submit" class="btn-submit-table" name="approve">Approve</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>

</html>

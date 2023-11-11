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

$query1 = $conn->query("SELECT * FROM admin");
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
    <h1 style="font-size:30px">EDIT ADMIN</h1>
  </div>
</div><br>
<div class="announcement-list">
    <div class="blue-box">
      <h2 style="color:#fff;font-weight:700;">Message</h2>
      <h2 style="color:#fff;">Keep update the club's profile regularly.</h2>
    </div>
  </div><br><br>
  
    <div class="form-container">
        <h2 style="font-size:20px">Insert New Admin</h2><br>
        <form name="frmContact" id="frmContact" method="post" action="" enctype="multipart/form-data">
            <div class="input-row">
                <label>Admin ID:</label>
                <input type="text" class="input-field" name="admin_id" placeholder="Enter student matric number" required><br>
            </div>
            <div class="input-row">
                <label>Admin Name:</label>
                <input type="text" class="input-field" name="admin_name" placeholder="Enter student name" required><br>
            </div>
            <div class="input-row">
                <label>Security Phrase:</label>
                <input type="text" class="input-field" name="security_phrase" placeholder="Enter student security phrase" required><br>
            </div>
            <div class="input-row">
                <label>Security Image:</label>
                <div id="dropzone">
                    <h2>Drag and drop files here or click to select files</p>
                    <input type="file" name="security_image" id="security_image" accept="image/*,video/*" multiple>
                </div>
            </div>

            <button type="submit" class="btn-submit-table" name="insert">Insert</button>
        </form>
    </div>
    <br><br><br><br><br><br><br>
    <?php
     if (isset($_POST['insert'])) {
        $admin_id = $_POST['admin_id'];
        $admin_name = $_POST['admin_name'];
        $security_phrase = $_POST['security_phrase'];

        if (empty($admin_id) || empty($admin_name) || empty($security_phrase)) {
            echo "Please fill in all fields.";
        } else {
            if (empty($_FILES['security_image']['tmp_name'])) {
                echo "Please upload an image.";
            } else {
                $imageType = $_FILES['security_image']['type'];
                $imageSize = $_FILES['security_image']['size'];

                // Validate image
                if (in_array($imageType, array('image/jpg', 'image/jpeg', 'image/png', 'image/gif')) && $imageSize < 1000000000) {
                    $security_image = $_FILES['security_image']['tmp_name'];
                    $uploadDirectory = $_SERVER['DOCUMENT_ROOT'] . '/faclub/admin/uploads/'; // use absolute path
                    $uploadedFileName = $_FILES['security_image']['name'];
                    $targetPath = $uploadDirectory . $uploadedFileName;

                    // Move uploaded file to the destination directory
                    if (move_uploaded_file($security_image, $targetPath)) {
                        $security_image = file_get_contents($targetPath);

                        

                        // Check for connection error
                        if ($conn->connect_error) {
                            die("Connection failed: An error occurred.");
                        }
                        

                        // Check if the email is already in use
                        $stmt = $conn->prepare("SELECT Admin_ID FROM admin WHERE Admin_ID = ?");
                        $stmt->bind_param("s", $admin_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            echo '<script>alert("Admin already exists!");</script>';
                        } else {
                            // Insert the user into the database
                            $stmt = $conn->prepare("INSERT INTO admin (Admin_ID, Role_ID, Admin_Name, Security_Phrase, Security_Image, Modified_On, Modified_By) VALUES (?, '1', ?, ?, ?, NOW(), ?)");
                            $stmt->bind_param("isssi", $admin_id, $admin_name, $security_phrase, $security_image, $user_admin);
                            

                            if ($stmt->execute()) {
                                echo "<script>alert('Admin inserted successfully.');</script>";
                                $logQuery = "INSERT INTO log_table (Admin_ID, Log_Type, Date, Time) VALUES ('$user_admin', 'Insert New Admin', CURDATE(), CURTIME())";
                                mysqli_query($conn, $logQuery);
                            } else {
                                echo "<script>alert('Failed to insert admin.');</script>";
                            }
                        }
                    } else {
                        echo "Failed to move the uploaded file.";
                    }
                } else {
                    echo '<script>alert("Invalid image type or size.");</script>';
                }
            }
        }
    }

 if (isset($_POST['update'])) {
    if (isset($_POST['admin_id'], $_POST['admin_name'], $_POST['security_phrase'])) {
        $admin_id = $_POST['admin_id'];
        $admin_name = $_POST['admin_name'];
        $security_phrase = $_POST['security_phrase'];
        $new_security_image = null;

        // Check if a new security image is selected
        if (isset($_FILES['new_security_image']) && $_FILES['new_security_image']['error'] == UPLOAD_ERR_OK && $_FILES['new_security_image']['size'] > 0) {
            $new_security_image = file_get_contents($_FILES['new_security_image']['tmp_name']);
        } else {
            // Use the current security image
            $current_security_image = base64_decode($_POST['current_security_image']);
            if ($current_security_image !== false) {
                $new_security_image = $current_security_image;
            }
        }  
        $security_image = '';
    if (isset($_FILES['security_image']) && $_FILES['security_image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "admin/uploads/";
        $target_file = $target_dir . basename($_FILES['security_image']['name']);

        // check for a valid file path
        if (strpos($target_file, "\0") !== false) {
            echo "<script>alert('Invalid file path.');</script>";
        } else {
            move_uploaded_file($_FILES['security_image']['tmp_name'], $target_file);
            $security_image = file_get_contents($target_file);
        }
    }
        // Update studentrecord
        $stmt = mysqli_prepare($conn, "UPDATE admin SET Admin_Name=?, Security_Phrase=?, Security_Image=?, Modified_By=?, Modified_On=NOW() WHERE Admin_ID=?");
        mysqli_stmt_bind_param($stmt, "sssii", $admin_name, $security_phrase, $new_security_image,  $user_admin, $admin_id);
        if (mysqli_stmt_execute($stmt)) {
            $logQuery = "INSERT INTO log_table (Admin_ID, Log_Type, Date, Time) VALUES ('$user_admin', 'Update Admin', CURDATE(), CURTIME())";
            mysqli_query($conn, $logQuery);
            echo "<script>alert('Admin record updated successfully.');</script>";
        } else {
            echo "<script>alert('Failed to update admin record.');</script>";
        }
        mysqli_stmt_close($stmt);
    }
}

  
    if (isset($_POST['delete'])) {
        $delete_id = $conn->real_escape_string($_POST['delete']);

        // Delete the row from the "application" table
        $delete_query = "DELETE FROM admin WHERE Admin_ID = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("s", $delete_id);

        // Execute the delete queries
        if ($delete_stmt->execute()) {
            $logQuery = "INSERT INTO log_table (Admin_ID, Log_Type, Date, Time) VALUES ('$user_admin', 'Delete Admin', CURDATE(), CURTIME())";
            mysqli_query($conn, $logQuery);
            echo "<script>alert('Deleted successfully!');</script>";
        } else {
            echo "<script>alert('Failed to delete data.');</script>";
        }
    }
    echo '<form class="search-form" method="post" action="">';
    echo '<input class="search-input" type="text" name="search_keyword" placeholder="Enter keyword">';
    echo '<button class="search-button" type="submit" name="search">Search</button>';
    echo '<a class="reload-link" href="edit-admin.php"><i class="fas fa-sync-alt"></i></a>';
    echo '</form>';
    // Check if search form submitted
    if (isset($_POST['search'])) {
        $keyword = $_POST['search_keyword'];

        // Modify your query to include the search keyword
        $query = "SELECT a.Admin_ID as admin_id, a.Admin_Name as admin_name, a.Security_Image as security_image, a.Security_Phrase as security_phrase, a.Modified_On as modified_on, b.Admin_Name as modified_by 
        FROM admin a JOIN admin b 
        ON b.Admin_ID = a.Modified_By 
        WHERE a.admin_name LIKE '%$keyword%'";

        // Execute the query
        $result = mysqli_query($conn, $query);

        // Display search results
        echo '<div class="search-results">';
        echo ' <h2 style="text-align:center;font-weight:700;">Search Results</h2><br>';
        echo '<div class="announcement-list">';
        echo '<table>';
        echo '<tr><th>Admin ID</th><th>Admin Name</th><th>Security Image</th><th>Security Phrase</th><th>Action</th><th>Modified On</th><th>Modified By</th>';

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                // Display table rows
                echo '<form method="post" action="" enctype="multipart/form-data">';
                echo '<tr>';
                echo '<td><input type="hidden" name="admin_id" value="' . $row['admin_id'] . '">' . $row['admin_id'] . '</td>';
                echo '<td><input type="hidden" name="admin_name" value="' . $row['admin_name'] . '">' . $row['admin_name'] . '</td>';
                echo '<td>';
                echo '<div style="display: flex; align-items: center;">';
                echo '<input type="file" name="new_security_image">';
                echo '<input type="hidden" name="current_security_image" value="' . base64_encode($row['security_image']) . '">';
                echo '<img style="width:100px;height:100px;border:1px solid #000;" src="data:image/jpeg;base64,' . base64_encode($row['security_image']) . '" />';
                echo '</div>';
                echo '</td>';
                echo '<td><input type="text" name="security_phrase" value="' . $row['security_phrase'] . '"></td>';
                echo '<td>';
                echo '<button type="submit" class="btn-submit-table" name="update">Update</button><br><br>';
                echo '<button type="submit" class="btn-delete-table" name="delete" value="' . $row['admin_id'] . '">Delete</button>';
                echo '</td>';
                echo '<td>' . $row['modified_on'] . '</td>';
                echo '<td><input type="hidden" name="modified_by" value="' . $row['modified_by'] . '">' . $row['modified_by'] . '</td>';
                echo '</tr>';
                echo '</form>';
            }
        } else {
            echo '<tr><td colspan="8">No results found.</td></tr>';
        }

        echo '</table>';
        echo '</div>';
        echo '</div>';
    } else {
        // Display existing table
        $query = "SELECT a.Admin_ID as admin_id, a.Admin_Name as admin_name, a.Security_Image as security_image, a.Security_Phrase as security_phrase, a.Modified_On as modified_on, b.Admin_Name as modified_by 
        FROM admin a JOIN admin b 
        ON b.Admin_ID = a.Modified_By";

        // Execute the query
        $existingResult = mysqli_query($conn, $query);
        echo '<div class="existing-table">';
        echo '<h2 style="text-align:center;font-weight:700;">List Admin</h2><br>';
        echo '<div class="announcement-list">';
        echo '<table>';
        echo '<tr><th>Admin ID</th><th>Admin Name</th><th>Security Image</th><th>Security Phrase</th><th>Action</th><th>Modified On</th><th>Modified By</th>';

        // Fetch and display existing table rows
        while ($row = mysqli_fetch_assoc($existingResult)) {
            echo '<form method="post" action="" enctype="multipart/form-data">';
                echo '<tr>';
                echo '<td><input type="hidden" name="admin_id" value="' . $row['admin_id'] . '">' . $row['admin_id'] . '</td>';
                echo '<td><input type="text" name="admin_name" value="' . $row['admin_name'] . '"></td>';
                echo '<td>';
                echo '<div style="display: flex; align-items: center;">';
                echo '<input type="file" name="new_security_image">';
                echo '<input type="hidden" name="current_security_image" value="' . base64_encode($row['security_image']) . '">';
                echo '<img style="width:100px;height:100px;border:1px solid #000;" src="data:image/jpeg;base64,' . base64_encode($row['security_image']) . '" />';
                echo '</div>';
                echo '</td>';
                echo '<td><input type="text" name="security_phrase" value="' . $row['security_phrase'] . '"></td>';
                echo '<td>';
                echo '<button type="submit" class="btn-submit-table" name="update">Update</button><br><br>';
                echo '<button type="submit" class="btn-delete-table" name="delete" value="' . $row['admin_id'] . '">Delete</button>';
                echo '</td>';
                echo '<td>' . $row['modified_on'] . '</td>';
                echo '<td><input type="hidden" name="modified_by" value="' . $row['modified_by'] . '">' . $row['modified_by'] . '</td>';
                echo '</tr>';
                echo '</form>';
        }

        echo '</table>';
        echo '</div>';
        echo '</div>';
    }


    $stmt = mysqli_prepare($conn, "SELECT a.Admin_ID as admin_id, a.Admin_Name as admin_name, a.Security_Image as security_image, a.Security_Phrase as security_phrase, a.Modified_On as modified_on, b.Admin_Name as modified_by 
    FROM admin a JOIN admin b 
    ON b.Admin_ID = a.Modified_By");

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
    } else {
        echo "<script>alert('Failed to retrieve the current image.');</script>";
    }
    mysqli_stmt_close($stmt);

    // 
    // close connection
    mysqli_close($conn);
    ?>
</body>
    <script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript">

    </script>
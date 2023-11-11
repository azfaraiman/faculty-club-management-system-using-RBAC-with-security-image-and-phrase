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
    <h1 style="font-size:30px">EDIT CLUB</h1>
  </div>
</div><br>
<div class="announcement-list">
    <div class="blue-box">
      <h2 style="color:#fff;font-weight:700;">Message</h2>
      <h2 style="color:#fff;">Keep update the club's profile regularly.</h2>
    </div>
  </div><br><br>

    <div class="form-container">
        <h2 style="font-size:20px">Insert New Club</h2><br>
        <form name="frmContact" id="frmContact" method="post" action="" enctype="multipart/form-data">
            <div class="input-row">
                <label>Club ID:</label>
                <input type="text" class="input-field" name="club_id" placeholder="Enter club id" required><br>
            </div>
            <div class="input-row">
                <label>Club Name:</label>
                <input type="text" class="input-field" name="club_name" placeholder="Enter club name" required><br>
            </div>
            <div class="input-row">
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

                </select>
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
    <br><br><br><br>
    <?php

    if (isset($_POST['insert'])) {
        $club_id = $_POST['club_id'];
        $club_name = $_POST['club_name'];
        $faculty = $_POST['faculty'];
        $security_phrase = $_POST['security_phrase'];

        if (empty($club_id) || empty($club_name) || empty($faculty) || empty($security_phrase)) {
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
                        $stmt = $conn->prepare("SELECT Club_ID, Faculty_ID FROM club WHERE Club_ID = ? OR Faculty_ID = ?");
                        $stmt->bind_param("ii", $club_id, $faculty);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            echo '<script>alert("Club already exists for this faculty!");</script>';
                        } else {
                            // Insert the user into the database
                            $stmt = $conn->prepare("INSERT INTO club (Club_ID, Role_ID, Club_Name, Faculty_ID, Security_Phrase, Security_Image, Modified_On, Admin_ID) VALUES (?, '3', ?, ?, ?, ?, NOW(), ?)");
                            $stmt->bind_param("ssissi", $club_id, $club_name, $faculty, $security_phrase, $security_image, $user_admin);

                            if ($stmt->execute()) {
                                $logQuery = "INSERT INTO log_table (Admin_ID, Log_Type, Club_ID, Date, Time) VALUES ('$user_admin', 'Insert New Club', '$club_id', CURDATE(), CURTIME())";
                                mysqli_query($conn, $logQuery);
                                echo "<script>alert('Club inserted successfully.');</script>";
                            } else {
                                echo "<script>alert('Failed to insert club.');</script>";
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
        if (isset($_POST['club_id'], $_POST['club_name'], $_POST['security_phrase'])) {
            $club_id = $_POST['club_id'];
            $club_name = $_POST['club_name'];
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
            // Update club record
            $stmt = mysqli_prepare($conn, "UPDATE club SET Club_Name=?, Security_Phrase=?, Security_Image=?, Admin_ID=?, Modified_On=NOW() WHERE Club_ID=?");
            mysqli_stmt_bind_param($stmt, "sssii", $club_name, $security_phrase, $new_security_image, $user_admin, $club_id);
            if (mysqli_stmt_execute($stmt)) {
                $logQuery = "INSERT INTO log_table (Admin_ID, Log_Type, Club_ID, Date, Time) VALUES ('$user_admin', 'Update Club', '$club_id', CURDATE(), CURTIME())";
                mysqli_query($conn, $logQuery);
                echo "<script>alert('Club record updated successfully.');</script>";
            } else {
                echo "<script>alert('Failed to update club record.');</script>";
            }
            mysqli_stmt_close($stmt);
        }
    }

    if (isset($_POST['delete'])) {
        $delete_id = $conn->real_escape_string($_POST['delete']);

        // Delete the row from the "application" table
        $delete_query = "DELETE FROM club WHERE Club_ID = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("s", $delete_id);

        // Execute the delete queries
        if ($delete_stmt->execute()) {
            $logQuery = "INSERT INTO log_table (Admin_ID, Log_Type, Club_ID, Date, Time) VALUES ('$user_admin', 'Delete Club', '$club_id', CURDATE(), CURTIME())";
            mysqli_query($conn, $logQuery);
            echo "<script>alert('Deleted successfully!');</script>";
        } else {
            echo "<script>alert('Failed to delete data.');</script>";
        }
    }

    echo '<form class="search-form" method="post" action="">';
    echo '<input class="search-input" type="text" name="search_keyword" placeholder="Enter keyword">';
    echo '<button class="search-button" type="submit" name="search">Search</button>';
    echo '<a class="reload-link" href="edit-club.php"><i class="fas fa-sync-alt"></i></a>';
    echo '</form>';

    if (isset($_POST['search'])) {
        $keyword = $_POST['search_keyword'];

        // Modify your query to include the search keyword
        $query = "SELECT f.Faculty_Name as faculty_name, c.Club_ID AS club_id, c.Club_Name AS club_name, c.Security_Image AS security_image, c.Security_Phrase AS security_phrase, c.Modified_On as modified_on, a.Admin_Name as admin_name 
    FROM club c
    JOIN faculty f ON c.Faculty_ID = f.Faculty_ID
    JOIN admin a ON c.Admin_ID = a.Admin_ID WHERE club_name LIKE '%$keyword%' OR faculty_name LIKE '%$keyword%' OR admin_name LIKE '%$keyword%'";

        // Execute the query
        $result = mysqli_query($conn, $query);

        // Display search results
        echo '<div class="search-results">';
        echo ' <h2 style="text-align:center;font-weight:700;">Search Results</h2><br>';
           echo '<div class="announcement-list">';
        echo '<table>';
        echo '<tr><th>Club ID</th><th>Club Name</th><th>Faculty</th><th>Security Image</th><th>Security Phrase</th><th>Action</th><th>Modified On</th><th>Modified By</th>';
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
            echo '<form method="post" action="" enctype="multipart/form-data">';
            echo '<tr>';
            echo '<td><input type="hidden" name="club_id" value="' . $row['club_id'] . '">' . $row['club_id'] . '</td>';
            echo '<td><input type="text" name="club_name" value="' . $row['club_name'] . '" style="width: 250px;"></td>';
            echo '<td>' . $row['faculty_name'] . '</td>';
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
            echo '<button type="submit" class="btn-delete-table" name="delete" value="' . $row['club_id'] . '">Delete</button>';
            echo '</td>';
            echo '<td>' . $row['modified_on'] . '</td>';
            echo '<td><input type="hidden" name="admin_name" value="' . $row['admin_name'] . '">' . $row['admin_name'] . '</td>';
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
        $query = "SELECT f.Faculty_Name as faculty_name, c.Club_ID AS club_id, c.Club_Name AS club_name, c.Security_Image AS security_image, c.Security_Phrase AS security_phrase, c.Modified_On as modified_on, a.Admin_Name as admin_name 
    FROM club c
    JOIN faculty f ON c.Faculty_ID = f.Faculty_ID
JOIN admin a ON c.Admin_ID = a.Admin_ID";

        // Execute the query
        $existingResult = mysqli_query($conn, $query);
        echo '<div class="existing-table">';
        echo ' <h2 style="text-align:center;font-weight:700;">List Club</h2><br>';
           echo '<div class="announcement-list">';
        echo '<table>';
        echo '<tr><th>Club ID</th><th>Club Name</th><th>Faculty</th><th>Security Image</th><th>Security Phrase</th><th>Action</th><th>Modified On</th><th>Modified By</th>';
        while ($row = mysqli_fetch_assoc($existingResult)) {
            echo '<form method="post" action="" enctype="multipart/form-data">';
            echo '<tr>';
            echo '<td><input type="hidden" name="club_id" value="' . $row['club_id'] . '">' . $row['club_id'] . '</td>';
            echo '<td><input type="text" name="club_name" value="' . $row['club_name'] . '" style="width: 250px;"></td>';
            echo '<td>' . $row['faculty_name'] . '</td>';
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
            echo '<button type="submit" class="btn-delete-table" name="delete" value="' . $row['club_id'] . '">Delete</button>';
            echo '</td>';
            echo '<td>' . $row['modified_on'] . '</td>';
            echo '<td><input type="hidden" name="admin_name" value="' . $row['admin_name'] . '">' . $row['admin_name'] . '</td>';
            echo '</tr>';
            echo '</form>';
        }
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }

    $stmt = mysqli_prepare($conn, "SELECT f.Faculty_Name as faculty_name, c.Club_ID AS club_id, c.Club_Name AS club_name, c.Security_Image AS security_image, c.Security_Phrase AS security_phrase, c.Modified_On as modified_on, a.Admin_Name as admin_name 
    FROM club c 
    JOIN faculty f ON c.Faculty_ID = f.Faculty_ID
    JOIN admin a ON c.Admin_ID = a.Admin_ID");

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
    } else {
        echo "<script>alert('Failed to retrieve the current logo.');</script>";
    }
    mysqli_stmt_close($stmt);

    // close connection
    mysqli_close($conn);
    ?>

    <script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript">

    </script>
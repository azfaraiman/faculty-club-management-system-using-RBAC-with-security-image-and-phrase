<?php
require_once 'connection.php';
require_once 'config.php';
require_once 'encryption.php';

if (session_status() == PHP_SESSION_NONE) {
	session_set_cookie_params([
		'lifetime' => 3600,
		'path' => '/',
		'domain' => '',
		'secure' => true, 
		'httponly' => true 
	]);

	session_start();
	
	if (!isset($_SESSION['username_club']) || !isset($_SESSION['faculty_club'])) {
		header('Location: ../login.php');
		exit(); 
	}

	if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
		session_unset();
		session_destroy();
		echo "<script>setTimeout(function(){ window.location.href = '../login.php'; }, 500);</script>";
		echo "<script>alert('Your session has expired. Please log in again.');</script>";
		exit();
	}
	
	$_SESSION['LAST_ACTIVITY'] = time();
}

$corepage = explode('/', $_SERVER['PHP_SELF']);
$corepage = end($corepage);
if ($corepage !== 'index-club.php') {
	$corepage = explode('.', $corepage);
	header('Location: index-club.php?page=' . $corepage[0]);
	exit();
}
$user_club = $_SESSION['username_club'];
$faculty_club = $_SESSION['faculty_club'];


if (!$conn) {
    die("Connection failed: An error occurred.");
}


$club_mission = '';
$club_vision = '';
$club_about = '';
$club_contact = '';
$club_email = '';
$club_telegram = '';
$club_instagram = '';
$club_twitter = '';
$club_facebook = '';
$club_youtube = '';
$club_logo = '';
$club_chart = '';
$decrypted_contact="";
$decrypted_email="";

$key = ENCRYPTION_KEY;
$iv = substr(INITIALIZATION_VECTOR, 0, 16);

if (isset($_POST['update-mission'])) {
	$club_mission = isset($_POST['Club_Mission']) ? $_POST['Club_Mission'] : '';

	if (empty($club_mission)) {
        echo "<script>alert('Club Mission field is required.');</script>";
    } else {
        // Sanitize club_mission input
        $club_mission = sanitizeInput($club_mission);
		$stmt = mysqli_prepare($conn, "UPDATE club SET Club_Mission = ? WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "ss", $club_mission, $faculty_club);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
		$stmt = mysqli_prepare($conn, "SELECT Club_Mission FROM club WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "s", $faculty_club);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $club_mission);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);
		$logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$user_club', 'Edit Mission', CURDATE(), CURTIME())";
		mysqli_query($conn, $logQuery);
		echo "<script>alert('Club Mission updated successfully!');</script>";
	}
}

if (isset($_POST['update-vision'])) {
	$club_vision = isset($_POST['Club_Vision']) ? $_POST['Club_Vision'] : '';

	if (empty($club_vision)) {
        echo "<script>alert('Club Vision field is required.');</script>";
    } else {
        // Sanitize club_vision input
        $club_vision = sanitizeInput($club_vision);
		$stmt = mysqli_prepare($conn, "UPDATE club SET Club_Vision = ? WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "ss", $club_vision, $faculty_club);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
		$stmt = mysqli_prepare($conn, "SELECT Club_Vision FROM club WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "s", $faculty_club);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $club_vision);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);
		$logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$user_club', 'Edit Vision', CURDATE(), CURTIME())";
		mysqli_query($conn, $logQuery);
		echo "<script>alert('Club Vision updated successfully!');</script>";
	}
}
if (isset($_POST['update-about'])) {
	$club_about = isset($_POST['Club_About']) ? $_POST['Club_About'] : '';

	if (empty($club_about)) {
        echo "<script>alert('Club About field is required.');</script>";
    } else {
        // Sanitize club_about input
        $club_about = sanitizeInput($club_about);
		$stmt = mysqli_prepare($conn, "UPDATE club SET Club_About = ? WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "ss", $club_about, $faculty_club);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
		$stmt = mysqli_prepare($conn, "SELECT Club_About FROM club WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "s", $faculty_club);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $club_about);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);
		$logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$user_club', 'Edit About', CURDATE(), CURTIME())";
		mysqli_query($conn, $logQuery);
		echo "<script>alert('Club About updated successfully!');</script>";
	}
}

if (isset($_POST['update-contact'])) {
	$club_contact = isset($_POST['Club_Contact']) ? $_POST['Club_Contact'] : '';
	$encrypted_contact = encryptData($club_contact, $key, $iv);
	if (empty($club_contact)) {
        echo "<script>alert('Club Contact field is required.');</script>";
    } else {
        $club_contact = sanitizeInput($club_contact);

		$stmt = mysqli_prepare($conn, "UPDATE club SET Club_Contact = ? WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "ss", $encrypted_contact, $faculty_club);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);

		$stmt = mysqli_prepare($conn, "SELECT Club_Contact FROM club WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "s", $faculty_club);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $encrypted_contact);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);

		$logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$user_club', 'Edit Contact', CURDATE(), CURTIME())";
		mysqli_query($conn, $logQuery);
		
		echo "<script>alert('Club Contact updated successfully!');</script>";
	}
}

if (isset($_POST['update-email'])) {
	
    $club_email = isset($_POST['Club_Email']) ? $_POST['Club_Email'] : '';
	$encrypted_email = encryptData($club_email, $key, $iv);
    if (empty($club_email)) {
        echo "<script>alert('Club Email field is required.');</script>";
    } else {
        $club_email = sanitizeInput($club_email);

        $stmt = mysqli_prepare($conn, "UPDATE club SET Club_Email = ? WHERE Club_ID = ?");
        mysqli_stmt_bind_param($stmt, "ss", $encrypted_email, $faculty_club);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Retrieve and decrypt the email for display
        $stmt = mysqli_prepare($conn, "SELECT Club_Email FROM club WHERE Club_ID = ?");
        mysqli_stmt_bind_param($stmt, "s", $faculty_club);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $encrypted_email);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        $logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$user_club', 'Edit Email', CURDATE(), CURTIME())";
        mysqli_query($conn, $logQuery);

        echo "<script>alert('Club Email updated successfully!');</script>";
    }
}

if (isset($_POST['update-telegram'])) {
	$club_telegram = isset($_POST['Club_Telegram']) ? $_POST['Club_Telegram'] : '';

	if (empty($club_telegram)) {
        echo "<script>alert('Club Telegram field is required.');</script>";
    } else {
		$club_telegram = sanitizeInput($club_telegram);
		$stmt = mysqli_prepare($conn, "UPDATE club SET Club_Telegram = ? WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "ss", $club_telegram, $faculty_club);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
		$stmt = mysqli_prepare($conn, "SELECT Club_Telegram FROM club WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "s", $faculty_club);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $club_telegram);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);
		$logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$user_club', 'Edit Telegram', CURDATE(), CURTIME())";
		mysqli_query($conn, $logQuery);
		echo "<script>alert('Club Telegram updated successfully!');</script>";
	}
}
if (isset($_POST['update-instagram'])) {
	$club_instagram = isset($_POST['Club_Instagram']) ? $_POST['Club_Instagram'] : '';

	if (empty($club_instagram)) {
        echo "<script>alert('Club Instagram field is required.');</script>";
    } else {
		$club_instagram = sanitizeInput($club_instagram);
		$stmt = mysqli_prepare($conn, "UPDATE club SET Club_Instagram = ? WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "ss", $club_instagram, $faculty_club);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
		$stmt = mysqli_prepare($conn, "SELECT Club_Instagram FROM club WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "s", $faculty_club);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $club_instagram);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);
		$logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$user_club', 'Edit Instagram', CURDATE(), CURTIME())";
		mysqli_query($conn, $logQuery);
		echo "<script>alert('Club Instagram updated successfully!');</script>";
	}
}
if (isset($_POST['update-facebook'])) {
	$club_facebook = isset($_POST['Club_Facebook']) ? $_POST['Club_Facebook'] : '';

	if (empty($club_facebook)) {
        echo "<script>alert('Club Facebook field is required.');</script>";
    } else {
		$club_facebook = sanitizeInput($club_facebook);
		$stmt = mysqli_prepare($conn, "UPDATE club SET Club_Facebook = ? WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "ss", $club_facebook, $faculty_club);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
		$stmt = mysqli_prepare($conn, "SELECT Club_Facebook FROM club WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "s", $faculty_club);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $club_facebook);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);
		$logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$user_club', 'Edit Facebook', CURDATE(), CURTIME())";
		mysqli_query($conn, $logQuery);
		echo "<script>alert('Club Facebook updated successfully!');</script>";
	}
}

if (isset($_POST['update-twitter'])) {
	$club_twitter = isset($_POST['Club_Twitter']) ? $_POST['Club_Twitter'] : '';

	if (empty($club_twitter)) {
        echo "<script>alert('Club Twitter field is required.');</script>";
    } else {
		$club_twitter = sanitizeInput($club_twitter);
		$stmt = mysqli_prepare($conn, "UPDATE club SET Club_Twitter = ? WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "ss", $club_twitter, $faculty_club);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
		$stmt = mysqli_prepare($conn, "SELECT Club_Twitter FROM club WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "s", $faculty_club);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $club_twitter);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);
		$logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$user_club', 'Edit Twitter', CURDATE(), CURTIME())";
		mysqli_query($conn, $logQuery);
		echo "<script>alert('Club Twitter updated successfully!');</script>";
	}
}
if (isset($_POST['update-youtube'])) {
	$club_youtube = isset($_POST['Club_Youtube']) ? $_POST['Club_Youtube'] : '';

	if (empty($club_youtube)) {
        echo "<script>alert('Club Youtube field is required.');</script>";
    } else {
		$club_youtube = sanitizeInput($club_youtube);
		$stmt = mysqli_prepare($conn, "UPDATE club SET Club_Youtube = ? WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "ss", $club_youtube, $faculty_club);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
		$stmt = mysqli_prepare($conn, "SELECT Club_Youtube FROM club WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "s", $faculty_club);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $club_youtube);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);
		$logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$user_club', 'Edit Youtube', CURDATE(), CURTIME())";
		mysqli_query($conn, $logQuery);
		echo "<script>alert('Club Youtube updated successfully!');</script>";
	}
}

if (isset($_POST['update-logo'])) {
	$club_logo = $_FILES['Club_Logo'];

	// Check if a new logo file has been uploaded
	if ($_FILES['Club_Logo']['name'] != '') {
		// Get the binary data of the uploaded logo file
		$new_club_logo = file_get_contents($_FILES['Club_Logo']['tmp_name']);

		// Prepare the database update statement
		$stmt = mysqli_prepare($conn, "UPDATE club SET Club_Logo = ? WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "ss", $new_club_logo, $faculty_club);

		// Execute the database update statement
		if (mysqli_stmt_execute($stmt)) {
			// Retrieve the updated logo from the database
			$stmt = mysqli_prepare($conn, "SELECT Club_Logo FROM club WHERE Club_ID = ?");
			mysqli_stmt_bind_param($stmt, "s", $faculty_club);
			if (mysqli_stmt_execute($stmt)) {
				$result = mysqli_stmt_get_result($stmt);
				$row = mysqli_fetch_assoc($result);
				$club_logo = $row['Club_Logo'];
				$logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$user_club', 'Edit Logo', CURDATE(), CURTIME())";
				mysqli_query($conn, $logQuery);
				echo "<script>alert('Club Logo updated successfully!');
				
			window.location.href = window.location.href;
				</script>";
			} else {
				echo "<script>alert('Failed to retrieve the updated logo.');</script>";
			}
		} else {
			echo "<script>alert('Failed to update the logo.');</script>";
		}

		mysqli_stmt_close($stmt);
	} else {
		// Display an error message if no file was uploaded
		echo "<script>alert('Please select an image to upload.');</script>";
	}
}

if (isset($_POST['update-chart'])) {
	$club_chart = $_FILES['Club_Chart'];

	// Check if a new logo file has been uploaded
	if ($_FILES['Club_Chart']['name'] != '') {
		// Get the binary data of the uploaded logo file
		$new_club_chart = file_get_contents($_FILES['Club_Chart']['tmp_name']);

		// Prepare the database update statement
		$stmt = mysqli_prepare($conn, "UPDATE club SET Club_Chart = ? WHERE Club_ID = ?");
		mysqli_stmt_bind_param($stmt, "ss", $new_club_chart, $faculty_club);

		// Execute the database update statement
		if (mysqli_stmt_execute($stmt)) {
			// Retrieve the updated logo from the database
			$stmt = mysqli_prepare($conn, "SELECT Club_Chart FROM club WHERE Club_ID = ?");
			mysqli_stmt_bind_param($stmt, "s", $faculty_club);
			if (mysqli_stmt_execute($stmt)) {
				$result = mysqli_stmt_get_result($stmt);
				$row = mysqli_fetch_assoc($result);
				$club_chart = $row['Club_Chart'];
				$logQuery = "INSERT INTO log_table (Club_ID, Log_Type, Date, Time) VALUES ('$user_club', 'Edit Chart', CURDATE(), CURTIME())";
				mysqli_query($conn, $logQuery);
				echo "<script>alert('Club Chart updated successfully!');
				
			window.location.href = window.location.href;
				</script>";
			} else {
				echo "<script>alert('Failed to retrieve the updated chart.');</script>";
			}
		} else {
			echo "<script>alert('Failed to update the chart.');</script>";
		}

		mysqli_stmt_close($stmt);
	} else {
		// Display an error message if no file was uploaded
		echo "<script>alert('Please select an image to upload.');</script>";
	}
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$stmt = mysqli_prepare($conn, "SELECT Club_Name, Club_Mission, Club_Vision, Club_About, Club_Contact, Club_Email, Club_Instagram, Club_Telegram, Club_Facebook, Club_Twitter, Club_Youtube, Club_Logo, Club_Chart FROM club WHERE Club_ID = ?");
mysqli_stmt_bind_param($stmt, "s", $faculty_club);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $club_name, $club_mission, $club_vision, $club_about, $encrypted_contact, $encrypted_email, $club_instagram, $club_telegram, $club_facebook, $club_twitter, $club_youtube, $club_logo, $club_chart);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

$decrypted_email = decryptData($encrypted_email, $key, $iv);
$decrypted_contact = decryptData($encrypted_contact, $key, $iv);

$stmt = mysqli_prepare($conn, "SELECT Club_Logo, Club_Chart FROM club WHERE Club_ID = ?");
mysqli_stmt_bind_param($stmt, "s", $faculty_club);
if (mysqli_stmt_execute($stmt)) {
	$result = mysqli_stmt_get_result($stmt);
	$row = mysqli_fetch_assoc($result);
	$club_logo = $row['Club_Logo'];
	$club_chart = $row['Club_Chart'];
} else {
	echo "<script>alert('Failed to retrieve the current logo.');</script>";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>

<!DOCTYPE html>
<html>

<head>
	<title>Add Note</title>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro">
	<link rel="stylesheet" type="text/css" href="../css/style.css">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">


	<script>
		  var selectedFilesLogo = [];

function handleLogoFileInputChange(event) {
  const files = event.target.files;

  for (let i = 0; i < files.length; i++) {
	const file = files[i];
	selectedFilesLogo.push(file);

	const fileItem = document.createElement('div');
	fileItem.classList.add('selected-file');

	if (file.type.startsWith('image/')) {
	  const img = document.createElement('img');
	  img.src = URL.createObjectURL(file);
	  img.classList.add('selected-image-logo');
	  fileItem.appendChild(img);
	}

	const fileName = document.createElement('p');
	fileName.textContent = file.name;
	fileItem.appendChild(fileName);

	document.getElementById('selected-files-logo').appendChild(fileItem);
  }
}

var selectedFilesChart = [];

function handleChartFileInputChange(event) {
  const files = event.target.files;

  for (let i = 0; i < files.length; i++) {
	const file = files[i];
	selectedFilesChart.push(file);

	const fileItem = document.createElement('div');
	fileItem.classList.add('selected-file');

	if (file.type.startsWith('image/')) {
	  const img = document.createElement('img');
	  img.src = URL.createObjectURL(file);
	  img.classList.add('selected-image-chart');
	  fileItem.appendChild(img);
	}

	const fileName = document.createElement('p');
	fileName.textContent = file.name;
	fileItem.appendChild(fileName);

	document.getElementById('selected-files-chart').appendChild(fileItem);
  }
}

	</script>

</head>

<body>
	<div class="wrapper">
		<div class="announcement-list">
			<div style="margin-top:20px;display: flex; justify-content: space-between; width: 80%" class="container header-container">
				<h1 style="font-size:30px">EDIT INFO</h1>
				<h3><?php echo $club_name; ?></h3><br>
			</div>
		</div>
	
	<div class="announcement-list">
		<div class="blue-box">
		<h2 style="color:#fff;font-weight:700;">Message</h2>
      <h2 style="color:#fff;">Keep update the club's profile regularly.</h2>
		</div>
	</div><br><br>
	<div class="announcement-list">
		<b>Edit Mission</b>
	</div>
	<br>
	<form name="frmContact" id="frmContact" method="post">
		<div class="announcement-list">
			<textarea class="form-control" id="message" name="Club_Mission" rows="5" maxlength="1000"><?php echo $club_mission; ?></textarea>

			<br>
			<input type="submit" name="update-mission" class="btn-submit" value="Update" />
		</div>
	</form>
	<br><br>
	<div class="announcement-list">
		<b>Edit Vision</b>
	</div>
	<br>
	<form name="frmContact" id="frmContact" method="post">
		<div class="announcement-list">
			<textarea class="form-control" id="message" name="Club_Vision" rows="5" maxlength="1000"><?php echo $club_vision; ?></textarea>

			<br>
			<input type="submit" name="update-vision" class="btn-submit" value="Update" />
		</div>
	</form>
	<br><br><div class="announcement-list">
		<b>Edit About</b>
	</div>
	<br>
	<form name="frmContact" id="frmContact" method="post">
		<div class="announcement-list">
			<textarea class="form-control" id="message" name="Club_About" rows="5" maxlength="1000"><?php echo $club_about; ?></textarea>

			<br>
			<input type="submit" name="update-about" class="btn-submit" value="Update" />
		</div>
	</form>
	<br><br>
	<form name="frmContact" id="frmContact" method="post">
		<div class="announcement-list">
			<b>Edit Social Media</b><br>
			<table>
				<tr>
					<th>Platform</th>
					<th>URL/Contact</th>
					<th>Action</th>
				</tr>
				<tr>
					<td>Contact</td>
					<td><input class="input-field" id="message" type="text" name="Club_Contact" value="<?php echo htmlspecialchars($decrypted_contact); ?>"></td>
					<td><input type="submit" name="update-contact" class="btn-submit-table" value="Update"></td>
				</tr>
				<tr>
					<td>Email</td>
					<td><input class="input-field" id="message" type="text" name="Club_Email" value="<?php echo $decrypted_email; ?>"></td>
					<td><input type="submit" name="update-email" class="btn-submit-table" value="Update"></td>
				</tr>
				<tr>
					<td>Telegram</td>
					<td><input class="input-field" id="message" type="text" name="Club_Telegram" value="<?php echo $club_telegram; ?>"></td>
					<td><input type="submit" name="update-telegram" class="btn-submit-table" value="Update"></td>
				</tr>
				<tr>
					<td>Instagram</td>
					<td><input class="input-field" id="message" type="text" name="Club_Instagram" value="<?php echo $club_instagram; ?>"></td>
					<td><input type="submit" name="update-instagram" class="btn-submit-table" value="Update"></td>
				</tr>
				<tr>
					<td>Facebook</td>
					<td><input class="input-field" id="message" type="text" name="Club_Facebook" value="<?php echo $club_facebook; ?>"></td>
					<td><input type="submit" name="update-facebook" class="btn-submit-table" value="Update"></td>
				</tr>
				<tr>
					<td>Twitter</td>
					<td><input class="input-field" id="message" type="text" name="Club_Twitter" value="<?php echo $club_twitter; ?>"></td>
					<td><input type="submit" name="update-twitter" class="btn-submit-table" value="Update"></td>
				</tr>
				<tr>
					<td>Youtube</td>
					<td><input class="input-field" id="message" type="text" name="Club_Youtube" value="<?php echo $club_youtube; ?>"></td>
					<td><input type="submit" name="update-youtube" class="btn-submit-table" value="Update"></td>
				</tr>
			</table>
		</div>
	</form>
	<br><br>
	<form method="post" action="" enctype="multipart/form-data">
		<div class="announcement-list">
			<b>Edit Club Logo</b><br>
		</div>
		<div class="announcement-list">
			<div id="dropzone">
				<h2>Drag and drop files here or click to select files</h2><br>
				<input type="file" name="Club_Logo" id="Club_Logo" accept="image/*" multiple class="file-input" onchange="handleLogoFileInputChange(event)">
				<label for="Club_Logo" class="file-label">Choose File</label>
				<div id="selected-files-logo"></div>
			</div>
			<?php if ($club_logo) : ?>
				<div class="image-preview">
					<img src="data:image/jpeg;base64,<?php echo base64_encode($club_logo); ?>" />
				</div>
			<?php endif; ?>
			<br>
			<input type="submit" name="update-logo" class="btn-submit" value="Update" /><br>
		</div><br>
	</form>
	<br><br>
	<form method="post" action="" enctype="multipart/form-data">
		<div class="announcement-list">
			<b>Edit Club Chart</b><br>
		</div>
		<div class="announcement-list">
			<div id="dropzone">
				<h2>Drag and drop files here or click to select files</h2><br>
				<input type="file" name="Club_Chart" id="Club_Chart" accept="image/*" multiple class="file-input" onchange="handleChartFileInputChange(event)">
				<label for="Club_Chart" class="file-label">Choose File</label>
				<div id="selected-files-chart"></div>
			</div>
			<?php if ($club_chart) : ?>
				<div class="image-preview">
					<img src="data:image/jpeg;base64,<?php echo base64_encode($club_chart); ?>" />
				</div>
			<?php endif; ?>
			<br>
			<input type="submit" name="update-chart" class="btn-submit" value="Update" /><br>
		</div><br>
	</form>
	</div>
</body>

</html>
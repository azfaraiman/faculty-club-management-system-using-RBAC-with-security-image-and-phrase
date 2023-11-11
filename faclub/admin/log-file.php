<?php
// Database connection details
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

// Retrieve admin information
$query1 = $conn->query("SELECT * FROM admin");
$row1 = $query1->fetch_assoc();

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: An error occurred.");
}


// SQL query to select all rows from the log table

$sql = "SELECT * FROM log_table ORDER BY Date DESC, Time DESC LIMIT 100";

// Execute the query
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
</head>

<body>
    <div class="wrapper">
        <div class="announcement-list">
            <div style="margin-top:20px;display: flex; justify-content: space-between; width: 80%" class="container header-container">
                <h1 style="font-size:30px">LOG TABLE</h1>
            </div>
        </div>
        <br>
        <div class="announcement-list">
            <div class="blue-box">
                <h2 style="color:#fff;font-weight:700;">Message</h2>
                <h2 style="color:#fff;">Keep updating the club's profile regularly.</h2>
            </div>
        </div>
        <br><br>

        <?php
        // Check if any rows are returned
        if ($result->num_rows > 0) {
            // Create a table to display the logs
            echo '<div class="announcement-list">';
            echo "<table>";
            echo "<tr><th>Log ID</th><th>Log Type</th><th>Description</th></tr>";

            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["Log_ID"] . "</td>";
                echo "<td>" . $row["Log_Type"] . "</td>";
                echo "<td>" . getLogDescription($row) . " at " . $row["Time"] . " " . date("d F Y", strtotime($row["Date"])) . "</td>";
                echo "</tr>";
            }

            echo "</table>";
            echo '</div>';
        } else {
            echo "No logs found";
        }

        // Close the connection
        $conn->close();

        // Function to get the log description
        function getLogDescription($row)
        {
            $adminId = $row["Admin_ID"];
            $studentId = $row["Student_ID"];
            $clubId = $row["Club_ID"];
            $applicantId = $row["Applicant_ID"];
            $logType = $row["Log_Type"];

            // Check if login or logout
            if (($adminId || $studentId || $clubId) && $logType === "Login") {
                if ($adminId) {
                    return "Admin ID $adminId logged in";
                } elseif ($studentId) {
                    return "Student ID $studentId logged in";
                } elseif ($clubId) {
                    return "Club ID $clubId logged in";
                }
            } elseif (($adminId || $studentId || $clubId) && $logType === "Logout") {
                if ($adminId) {
                    return "Admin ID $adminId logged out";
                } elseif ($studentId) {
                    return "Student ID $studentId logged out";
                } elseif ($clubId) {
                    return "Club ID $clubId logged out";
                }
            } else {
                // Check if update password or register club
                if ($adminId && $logType === "Insert New Admin") {
                    return "Admin ID $adminId inserted new admin";
                } elseif ($adminId && $logType === "Update Admin") {
                    return "Admin ID $adminId updated admin data";
                }elseif ($adminId && $logType === "Delete Admin") {
                    return "Admin ID $adminId deleted admin";
                }elseif ($clubId && $logType === "Edit About") {
                    return "Club ID $clubId edited the club about";
                }elseif ($clubId && $logType === "Edit Vision") {
                    return "Club ID $clubId edited the club vision";
                }elseif ($clubId && $logType === "Edit Mission") {
                    return "Club ID $clubId edited the club mission";
                }elseif ($clubId && $logType === "Edit Contact") {
                    return "Club ID $clubId edited the club contact";
                }elseif ($clubId && $logType === "Edit Email") {
                    return "Club ID $clubId edited the club email";
                }elseif ($clubId && $logType === "Edit Telegram") {
                    return "Club ID $clubId edited the club telegram link";
                }elseif ($clubId && $logType === "Edit Instagram") {
                    return "Club ID $clubId edited the club instagram link";
                }elseif ($clubId && $logType === "Edit Facebook") {
                    return "Club ID $clubId edited the club facebook link";
                }elseif ($clubId && $logType === "Edit Twitter") {
                    return "Club ID $clubId edited the club twitter link";
                }elseif ($clubId && $logType === "Edit Youtube") {
                    return "Club ID $clubId edited the club youtube link";
                }elseif ($clubId && $logType === "Edit Logo") {
                    return "Club ID $clubId edited the club logo";
                }elseif ($clubId && $logType === "Edit Chart") {
                    return "Club ID $clubId edited the club chart";
                }elseif ($clubId && $logType === "Delete Media") {
                    return "Club ID $clubId deleted media";
                }elseif ($clubId && $logType === "Insert Media") {
                    return "Club ID $clubId inserted new media";
                }elseif ($clubId && $logType === "Delete Announcement") {
                    return "Club ID $clubId deleted announcement";
                }elseif ($clubId && $logType === "Insert Announcement") {
                    return "Club ID $clubId inserted new announcement";
                }elseif ($clubId && $logType === "Update Status Application") {
                    return "Club ID $clubId updated status application";
                }
                else {
                    // Check if there are two IDs present
                    if (($adminId && $studentId) || ($adminId && $clubId) || ($studentId && $clubId) || ($adminId && $applicantId) || ($studentId && $applicantId) || ($clubId && $applicantId)) {
                        if ($adminId && $studentId && $logType === "Approve Student") {
                            return "Admin ID $adminId approve Student ID $studentId";
                        } elseif ($adminId && $clubId && $logType === "Insert New Club") {
                            return "Admin ID $adminId inserted new Club with ID $clubId";
                        } elseif ($adminId && $clubId && $logType === "Update Club") {
                            return "Admin ID $adminId updated data for Club ID $clubId";
                        }elseif ($adminId && $clubId && $logType === "Delete Club") {
                            return "Admin ID $adminId deleted Club with ID $clubId";
                        }elseif ($clubId && $applicantId && $logType === "Delete Applicant") {
                            return "Club ID $clubId deleted Applicant ID $applicantId";
                        }elseif ($clubId && $applicantId && $logType === "Update Confirmation") {
                            return "Club ID $clubId updated confirmation data for Applicant ID $applicantId";
                        }elseif ($clubId && $applicantId && $logType === "Delete Application") {
                            return "Club ID $clubId deleted application for Applicant ID $applicantId";
                        }elseif ($clubId && $applicantId && $logType === "Update Interview") {
                            return "Club ID $clubId updated interview data for Applicant ID $applicantId";
                        }elseif ($studentId && $clubId && $logType === "Submit Application") {
                            return "Student ID $studentId submitted appliction for Club ID $clubId";
                        }
                        elseif ($studentId && $clubId) {
                            return "Student ID $studentId deleted Club ID $clubId";
                        }
                    } else {
                        // Default action for other log types
                        return "Admin ID $adminId performed an action";
                    }
                }
            }
        }
        ?>
    </div>
</body>

</html>

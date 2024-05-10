<?php
session_start();

// Include database connection
include_once "db_connection.php";

// Include PHPMailer autoload file
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if database connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch vehicles from the database
$sql = "SELECT * FROM vehicles";
$result = $conn->query($sql);

// Counter variable for serial numbers
$counter = 1;

// Array to store vehicles with next service dates within the next 7 days
$upcomingServiceVehicles = array();

// Calculate date range for next 7 days
$today = new DateTime();
$nextSevenDays = new DateTime();
$nextSevenDays->modify('+7 days');

// Loop through the vehicles to find upcoming service dates
while ($row = $result->fetch_assoc()) {
    $nextServiceDate = calculateNextServiceDate($row["engine_type"], $row["last_service_date"]);
    if ($nextServiceDate !== "") {
        $nextServiceDateTime = new DateTime($nextServiceDate);
        if ($nextServiceDateTime >= $today && $nextServiceDateTime < $nextSevenDays) {
            $upcomingServiceVehicles[] = array(
                'brand' => $row['brand'],
                'next_service_date' => $nextServiceDate
            );
        } elseif ($nextServiceDateTime->format('Y-m-d') == $today->format('Y-m-d')) {
            // Include vehicles with next service date as today's date
            $upcomingServiceVehicles[] = array(
                'brand' => $row['brand'],
                'next_service_date' => $nextServiceDate
            );
        }
    }
}

// Send notification email as soon as the dashboard is accessed
sendNotificationEmail();

// Function to send email notification to admin
function sendNotificationEmail()
{
    global $upcomingServiceVehicles;

    // Instantiate PHPMailer
    $mail = new PHPMailer(true); // Passing true enables exceptions

    try {
        // Set up SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Specify your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'dhanushm4422@gmail.com'; // SMTP username
        $mail->Password = 'cktiotzrxfhvddbg'; // SMTP password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Sender and recipient
        $mail->setFrom('dhanushm4422@gmail.com', 'Dhanush M');
        $mail->addAddress('nandhinis.21msc@kongu.edu', 'Admin');

        // Email subject and body
        $mail->Subject = 'Upcoming Services within 7 days';
        $mail->Body = generateEmailBody();

        // Send the email
        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Function to generate email body with upcoming service notifications
function generateEmailBody()
{
    global $upcomingServiceVehicles;

    $body = '';

    if (is_array($upcomingServiceVehicles) && count($upcomingServiceVehicles) > 0) {
        $body .= "List of Services:\n\n";
        foreach ($upcomingServiceVehicles as $vehicle) {
            $body .= $vehicle['brand'] . ' - Next Service Date: ' . $vehicle['next_service_date'] . "\n";
        }
    } else {
        $body .= 'No upcoming services.';
    }

    return $body;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Maintenance Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: "Poppins", sans-serif;
        }

        .navbar {
            background-color: #004080;
            color: #ffffff;
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.5rem;
        }

        .navbar-nav .nav-link {
            color: #ffffff;
            font-weight: 500;
        }

        .container-fluid {
            margin-top: 20px;
        }

        .table-scrollable {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
        }

        .btn {
            border-radius: 4px;
            font-weight: 600;
        }

        .light-green-bg {
            background-color: #d4edda !important;
        }

        .search-container {
            margin-bottom: 20px;
            margin-top: 10px;
        }

        .search-container input[type=text] {
            padding: 10px;
            margin-top: 10px;
            margin-bottom: 10px;
            width: 200px;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .footer {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            background-color: #004080;
            color: white;
            text-align: center;
            padding: 10px 0;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <!-- Logo and Brand -->
            <a class="navbar-brand" href="#">
                <img src="img/1.png" alt="Logo" height="40">
                <span class="ml-2">KEC Vehicle Maintenance Dashboard</span>
            </a>
            <!-- Navbar Toggler Button -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Items -->
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Right-aligned items -->
                <ul class="navbar-nav ml-auto">
                    <!-- Add Vehicle Link -->
                    <li class="nav-item">
                        <a class="nav-link" href="add_vehicle_form.php"><i class="fas fa-plus"></i> Add Vehicle&nbsp;</a>
                    </li>
                    <!-- Search Input -->
                    <li class="nav-item">
                        <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search..." class="form-control">
                    </li>&nbsp;
                    <!-- Notification Icon with Red Mark -->
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="notificationIcon" data-toggle="modal" data-target="#notificationModal" style="background-color: <?php echo count($upcomingServiceVehicles) > 0 ? 'red' : 'transparent'; ?>">
                            <i class="fas fa-bell"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <br><br>
    <!-- Main Content -->
    <div class="container-fluid">
        <!-- Existing Vehicles Table -->
        <div class="table-scrollable">
            <div class="table-responsive">
                <table id="vehicleTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Engine Type</th>
                            <th>Last Service Date</th>
                            <th>Next Service Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Reset counter
                        $counter = 1;
                        // Reset result pointer
                        mysqli_data_seek($result, 0);
                        // Output vehicle data
                        while ($row = $result->fetch_assoc()) : ?>
                            <tr id="row<?= $row['id'] ?>" class="<?= $row['service_done'] ? 'light-green-bg' : '' ?>">
                                <td><?= $counter++ ?></td>
                                <td><?= $row["brand"] ?></td>
                                <td><?= $row["model"] ?></td>
                                <td><?= $row["engine_type"] ?></td>
                                <td><?= $row["last_service_date"] ?></td>
                                <!-- Calculate and display next service date -->
                                <td><?= calculateNextServiceDate($row["engine_type"], $row["last_service_date"]) ?></td>
                                <td>
                                    <a href="edit_vehicle.php?id=<?= $row["id"] ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="delete_vehicle.php?id=<?= $row["id"] ?>" onclick="return confirm('Are you sure you want to delete this vehicle?');" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</a>

                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <br><br>
    <!-- Notification Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Upcoming Service Notifications</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if (count($upcomingServiceVehicles) > 0) : ?>
                        <ul>
                            <?php foreach ($upcomingServiceVehicles as $vehicle) : ?>
                                <li><?= $vehicle['brand'] ?> - Next Service Date: <?= $vehicle['next_service_date'] ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <p>No upcoming service notifications.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2024 KEC Vehicle Maintenance. All rights reserved.</p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Custom JavaScript for Search Functionality -->
    <script>
        function searchTable() {
            // Declare variables
            var input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("vehicleTable");
            tr = table.getElementsByTagName("tr");

            // Loop through all table rows, and hide those who don't match the search query
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break; // Break the inner loop when a match is found
                        } else {
                            tr[i].style.display = "none";
                        }
                    }
                }
            }
        }

        // Function to mark service as done and update row color
        function markServiceDone(vehicleId) {
            if (confirm('Are you sure you want to mark this service as done?')) {
                // Send an AJAX request to mark the service as done
                $.ajax({
                    url: 'mark_service_done.php', // Replace 'mark_service_done.php' with your actual script URL
                    method: 'POST',
                    data: {
                        vehicle_id: vehicleId
                    },
                    success: function(response) {
                        if (response === 'success') {
                            // If service is marked as done successfully, update row color
                            $('#row' + vehicleId).addClass('light-green-bg');
                            // Disable the service button after it's clicked
                            $('#serviceBtn' + vehicleId).prop('disabled', true);
                        } else {
                            // Handle error
                            console.error('Failed to mark service as done.');
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle error
                        console.error(error);
                    }
                });
            }
        }
    </script>
</body>

</html>

<?php
// Function to calculate next service date based on engine type and last service date
function calculateNextServiceDate($engineType, $lastServiceDate)
{
    // Convert last service date to DateTime object
    $lastServiceDateTime = new DateTime($lastServiceDate);

    // Calculate next service date based on engine type
    if ($engineType == "Old") {
        // Add 18 months for old engine type
        $nextServiceDateTime = $lastServiceDateTime->add(new DateInterval('P18M'));
    } elseif ($engineType == "New") {
        // Add 12 months for new engine type
        $nextServiceDateTime = $lastServiceDateTime->add(new DateInterval('P12M'));
    } else {
        // Default to empty string if engine type is not recognized
        return "";
    }

    // Format and return the next service date
    return $nextServiceDateTime->format('Y-m-d');
}
?>
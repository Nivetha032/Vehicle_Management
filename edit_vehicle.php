<?php
session_start();

// Include database connection
include_once "db_connection.php";

// Check if database connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if vehicle ID is provided in the URL
if (isset($_GET['id'])) {
    $vehicle_id = $_GET['id'];

    // Retrieve vehicle details from the database based on the provided ID
    $sql = "SELECT * FROM vehicles WHERE id = '$vehicle_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Vehicle found, display the form for editing
        $row = $result->fetch_assoc();
        $brand = $row["brand"];
        $model = $row["model"];
        $engine_type = $row["engine_type"];
        $last_service_date = $row["last_service_date"];
        $next_service_date = $row["next_service_date"]; // Retrieve next service date from the database
        $service_done = $row["service_done"]; // Retrieve service done status
    } else {
        // Vehicle not found, redirect to dashboard
        header("Location: dashboard.php");
        exit;
    }
} else {
    // Vehicle ID not provided, redirect to dashboard
    header("Location: dashboard.php");
    exit;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $brand = $_POST["brand"];
    $model = $_POST["model"];
    $engine_type = $_POST["engine_type"];
    $last_service_date = $_POST["last_service_date"];

    // Recalculate next service date based on the updated last service date and engine type
    $next_service_date = calculateNextServiceDate($engine_type, $last_service_date);

    // Update vehicle details in the database, including the recalculated next service date
    $sql_update_vehicle = "UPDATE vehicles SET brand = '$brand', model = '$model', engine_type = '$engine_type', last_service_date = '$last_service_date', next_service_date = '$next_service_date' WHERE id = '$vehicle_id'";

    if ($conn->query($sql_update_vehicle) === TRUE) {
        // Check if service done checkbox is checked
        $service_done = isset($_POST["service_done"]) ? 1 : 0;
        // Update service done status in the database
        $sql_update_service_done = "UPDATE vehicles SET service_done = $service_done WHERE id = '$vehicle_id'";
        if ($conn->query($sql_update_service_done) === TRUE) {
            // Redirect back to dashboard after updating vehicle
            header("Location: dashboard.php");
            exit;
        } else {
            echo "Error updating service done status: " . $conn->error;
        }
    } else {
        echo "Error updating vehicle: " . $conn->error;
    }
}

// Close database connection
$conn->close();

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
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vehicle</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: url('img/bus.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
        }

        .container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            animation: slideIn 0.5s ease forwards;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h2 {
            color: #004080;
            font-weight: 600;
            text-align: center;
            margin-bottom: 30px;
        }

        label {
            color: #495057;
            font-weight: 500;
        }

        input[type="text"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        select:focus {
            border-color: #007bff;
        }

        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="8" height="4"><path d="M0 0l4 4 4-4z"/></svg>');
            background-repeat: no-repeat;
            background-position-x: calc(100% - 12px);
            background-position-y: center;
            padding-right: 30px;
        }

        .btn-success {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .btn-success:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Edit Vehicle</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $vehicle_id; ?>" method="post">
            <div class="form-group">
                <label for="brand">Brand:</label>
                <input type="text" name="brand" id="brand" class="form-control" value="<?= $brand ?>" required>
            </div>
            <div class="form-group">
                <label for="model">Model:</label>
                <input type="text" name="model" id="model" class="form-control" value="<?= $model ?>" required>
            </div>
            <div class="form-group">
                <label for="engine_type">Engine Type:</label>
                <select name="engine_type" id="engine_type" class="form-control" required>
                    <option value="Old" <?= ($engine_type == 'Old') ? 'selected' : '' ?>>Old</option>
                    <option value="New" <?= ($engine_type == 'New') ? 'selected' : '' ?>>New</option>
                </select>
            </div>
            <div class="form-group">
                <label for="last_service_date">Last Service Date:</label>
                <input type="date" name="last_service_date" id="last_service_date" class="form-control" value="<?= $last_service_date ?>" required>
            </div>
            <div class="form-group">
                <input type="checkbox" id="service_done" name="service_done" <?= ($service_done == 1) ? 'checked' : '' ?>>
                <label for="service_done">Service Done</label>
            </div>
            <button type="submit" class="btn btn-success">Update Vehicle</button>
        </form>
    </div>
</body>

</html>
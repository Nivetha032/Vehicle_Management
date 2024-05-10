<?php
session_start();

// Include database connection
include_once "db_connection.php";

// Check if database connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $brand = $_POST["brand"];
    $model = $_POST["model"];
    $engine_type = $_POST["engine_type"];
    $last_service_date = $_POST["last_service_date"];

    // Calculate next service date based on engine type
    if ($engine_type === "Old") {
        $next_service_date = date('Y-m-d', strtotime($last_service_date . ' + 18 months'));
    } elseif ($engine_type === "New") {
        $next_service_date = date('Y-m-d', strtotime($last_service_date . ' + 12 months'));
    } else {
        // Handle unsupported engine types if needed
        $next_service_date = null;
    }

    // Insert data into the database
    $sql = "INSERT INTO vehicles (brand, model, engine_type, last_service_date, next_service_date) VALUES ('$brand', '$model', '$engine_type', '$last_service_date', '$next_service_date')";
    if ($conn->query($sql) === TRUE) {
        // Redirect back to dashboard
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vehicle</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
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
        <h2>Add New Vehicle</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="brand">Brand:</label>
                <input type="text" name="brand" id="brand" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="model">Model:</label>
                <input type="text" name="model" id="model" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="engine_type">Engine Type:</label>
                <select name="engine_type" id="engine_type" class="form-control" required>
                    <option value="Old">Old</option>
                    <option value="New">New</option>
                </select>
            </div>
            <div class="form-group">
                <label for="last_service_date">Last Service Date:</label>
                <input type="date" name="last_service_date" id="last_service_date" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Add Vehicle</button>
        </form>
    </div>
</body>

</html>

<?php
// Close database connection
$conn->close();
?>
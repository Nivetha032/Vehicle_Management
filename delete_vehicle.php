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

    // Delete vehicle from the database based on the provided ID
    $sql_delete_vehicle = "DELETE FROM vehicles WHERE id = '$vehicle_id'";

    if ($conn->query($sql_delete_vehicle) === TRUE) {
        // Redirect back to dashboard after deleting vehicle
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Error deleting vehicle: " . $conn->error;
    }
} else {
    // Vehicle ID not provided, redirect to dashboard
    header("Location: dashboard.php");
    exit;
}

// Close database connection
$conn->close();

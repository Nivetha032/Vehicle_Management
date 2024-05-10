<?php
session_start();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Hardcoded admin credentials for demonstration, replace these with your actual admin credentials
    $admin_username = "admin";
    $admin_password = "password";

    // Retrieve username and password from the form
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Check if username and password match the admin credentials
    if ($username === $admin_username && $password === $admin_password) {
        // Set session variables
        $_SESSION["username"] = $username;
        // Redirect to dashboard page
        header("Location: dashboard.php");
        exit;
    } else {
        // Display error message if credentials are incorrect
        echo "Invalid username or password. Please try again.";
    }
}

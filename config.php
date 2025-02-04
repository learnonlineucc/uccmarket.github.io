<?php
// config.php
$servername = "localhost";
$username = "root";    // Your MySQL username (usually 'root' for local development)
$password = "";        // Your MySQL password (usually empty in local XAMPP setups)
$dbname = "ucc_market"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

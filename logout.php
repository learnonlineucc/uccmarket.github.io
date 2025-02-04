<?php
session_start(); // Start the session

// Destroy all session data
session_unset(); // Clears session variables
session_destroy(); // Destroys the session

// Redirect to login page
header("Location: login.php");
exit();
?>

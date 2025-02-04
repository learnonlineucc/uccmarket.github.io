<?php
session_start();
require 'config.php';

// Check if buyer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

// Get form data
$full_name = $_POST['full_name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$level = $_POST['level'];
$program = $_POST['program'];
$password = $_POST['password'];

// Validate and sanitize inputs
if (empty($full_name) || empty($email) || empty($phone) || empty($level) || empty($program)) {
    die("Please fill in all required fields.");
}

// Update password if provided
if (!empty($password)) {
    $password = password_hash($password, PASSWORD_DEFAULT);  // Hash the password
    $update_query = "UPDATE users SET full_name = ?, email = ?, phone = ?, level = ?, program = ?, password = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssssssi", $full_name, $email, $phone, $level, $program, $password, $_SESSION['user_id']);
} else {
    // If no password is provided, update only other details
    $update_query = "UPDATE users SET full_name = ?, email = ?, phone = ?, level = ?, program = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssssi", $full_name, $email, $phone, $level, $program, $_SESSION['user_id']);
}

$stmt->execute();

// Redirect to the profile page with a success message
header("Location: profile.php?message=Profile updated successfully");
exit();
?>

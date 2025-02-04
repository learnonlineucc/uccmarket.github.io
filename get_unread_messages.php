<?php
session_start();
require 'config.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    exit();
}

$seller_id = $_SESSION['user_id'];

// Count unread messages for the seller
$stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND status = 'unread'");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$stmt->bind_result($unread_count);
$stmt->fetch();
$stmt->close();

// Return unread message count
echo $unread_count;
?>

<?php
session_start();
require 'config.php';

// Ensure the user is logged in as a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];
$buyer_id = isset($_GET['buyer_id']) ? intval($_GET['buyer_id']) : null;

if (!$buyer_id) {
    header("Location: chatlist.php?error=Invalid request");
    exit();
}

// Check if the chat exists
$check_stmt = $conn->prepare("
    SELECT COUNT(*) AS chat_count FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
");
$check_stmt->bind_param("iiii", $seller_id, $buyer_id, $buyer_id, $seller_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$chat_count = $check_result->fetch_assoc()['chat_count'];

if ($chat_count > 0) {
    // Delete messages if chat exists
    $delete_stmt = $conn->prepare("
        DELETE FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
    ");
    $delete_stmt->bind_param("iiii", $seller_id, $buyer_id, $buyer_id, $seller_id);
    
    if ($delete_stmt->execute()) {
        header("Location: chatlist.php?success=Chat deleted successfully");
    } else {
        header("Location: chatlist.php?error=Failed to delete chat");
    }
} else {
    header("Location: chatlist.php?error=Chat not found");
}
exit();
?>

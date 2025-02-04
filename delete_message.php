<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message_id'])) {
    $message_id = $_POST['message_id'];

    // Ensure only the sender or receiver can delete the message
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND (sender_id = ? OR receiver_id = ?)");
    $stmt->bind_param("iii", $message_id, $_SESSION['user_id'], $_SESSION['user_id']);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Message deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting message.";
    }
}

// Redirect back to messages page
header("Location: messages.php");
exit();
?>

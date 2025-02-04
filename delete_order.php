<?php
session_start();
require 'config.php';

// Check if the user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

// Check if an order ID is provided
if (isset($_GET['id'])) {
    $order_id = $_GET['id'];

    // Prepare the DELETE query to remove the order from the database
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ? AND seller_id = ?");
    $stmt->bind_param("ii", $order_id, $_SESSION['user_id']); // Ensures only the seller can delete their orders
    if ($stmt->execute()) {
        // Redirect back to the seller dashboard after successful deletion
        header("Location: seller_dashboard.php?msg=Order deleted successfully.");
        exit();
    } else {
        // If deletion fails, redirect back with an error message
        header("Location: seller_dashboard.php?msg=Error deleting order.");
        exit();
    }
} else {
    // If no order ID is provided, redirect to the dashboard with an error
    header("Location: seller_dashboard.php?msg=Invalid order ID.");
    exit();
}
?>

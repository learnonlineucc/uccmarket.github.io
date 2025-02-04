<?php
session_start();
require 'config.php';

// Check if user is logged in as seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

// Get product ID from URL
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Get product image path to delete the file
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($image);
    $stmt->fetch();
    $stmt->close();

    // Delete product from database
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        // Remove the product image from the server
        if (file_exists($image)) {
            unlink($image);
        }
        $_SESSION['success'] = "Product deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting product!";
    }

    $stmt->close();
    header("Location: seller_dashboard.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid product!";
    header("Location: seller_dashboard.php");
    exit();
}
?>

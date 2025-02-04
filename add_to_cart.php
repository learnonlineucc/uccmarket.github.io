<?php
include('config.php');
session_start();

// Ensure the user is logged in and is a buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: login.php");
    exit();
}

// Get user_id from session
$user_id = $_SESSION['user_id'];

// Get product_id from GET parameter
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : 0;
$quantity = 1; // Default quantity to 1, this can be updated if needed

if ($product_id) {
    // Check if the product is already in the cart
    $sql = "SELECT * FROM cart WHERE buyer_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id); // Bind parameters
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Product already exists in the cart, update the quantity
        $sql_update = "UPDATE cart SET quantity = quantity + 1 WHERE buyer_id = ? AND product_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ii", $user_id, $product_id); // Bind parameters
        $stmt_update->execute();
    } else {
        // Add product to cart
        $sql_insert = "INSERT INTO cart (buyer_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iii", $user_id, $product_id, $quantity); // Bind parameters
        $stmt_insert->execute();
    }

    // Redirect to cart page
    header("Location: view_cart.php");
    exit();
} else {
    // If no product_id is passed, redirect to the dashboard
    header("Location: buyer_dashboard.php");
    exit();
}
?>

<?php
session_start();
require 'config.php';

// Check if buyer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

// Get the payment details
$payment_method = $_POST['payment_method'];
$phone_number = $_POST['phone_number'];
$amount = $_POST['amount'];

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: buyer_dashboard.php");
    exit();
}

// Process payment (you can add API integration with MoMo or TeleCash here)

$order_status = 'pending'; // Initially set order status as pending

// Store order in the database
$order_query = "INSERT INTO orders (buyer_id, amount, payment_method, phone_number, status) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("iisss", $_SESSION['user_id'], $amount, $payment_method, $phone_number, $order_status);
$stmt->execute();

// Get the last inserted order ID
$order_id = $stmt->insert_id;

// Store each product in the order details table
foreach ($_SESSION['cart'] as $product_id) {
    $order_detail_query = "INSERT INTO order_details (order_id, product_id) VALUES (?, ?)";
    $stmt = $conn->prepare($order_detail_query);
    $stmt->bind_param("ii", $order_id, $product_id);
    $stmt->execute();
}

// Clear the cart after the order is placed
unset($_SESSION['cart']);

// Redirect to order confirmation page
header("Location: order_confirmation.php?order_id=$order_id");
exit();
?>

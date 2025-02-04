<?php
session_start();
require 'config.php';

// Check if buyer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

$order_id = $_GET['order_id'];
$query = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="order-confirmation">
        <h2>Order Confirmation</h2>
        <p>Thank you for your order! Your order ID is: <strong><?php echo $order['id']; ?></strong></p>
        <p>Status: <strong><?php echo $order['status']; ?></strong></p>
        <p>Total Amount: <strong>GH₵<?php echo number_format($order['amount'], 2); ?></strong></p>
        <p>Payment Method: <strong><?php echo ucfirst($order['payment_method']); ?></strong></p>
        <p>Seller's Phone Number: <strong><?php echo $order['phone_number']; ?></strong></p>
    </div>
</body>
</html>

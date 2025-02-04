<?php
session_start();
require 'config.php';

// Ensure that the form data is received
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $buyer_id = $_SESSION['user_id']; // Get buyer's ID from the session
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $total_price = $_POST['total_price'];

    // Insert the order into the orders table
    $order_stmt = $conn->prepare("
        INSERT INTO orders (buyer_id, total_price, order_date, status, address, phone, email) 
        VALUES (?, ?, NOW(), 'Pending', ?, ?, ?)
    ");
    $order_stmt->bind_param("idsss", $buyer_id, $total_price, $address, $phone, $email);
    $order_stmt->execute();
    $order_id = $order_stmt->insert_id; // Get the newly inserted order ID

    // Insert the order items (product and quantity)
    $order_items_stmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price) 
        VALUES (?, ?, ?, ?)
    ");
    $order_items_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $total_price);
    $order_items_stmt->execute();

    // Fetch seller's email for notification
    $product_stmt = $conn->prepare("SELECT seller_id FROM products WHERE id = ?");
    $product_stmt->bind_param("i", $product_id);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();
    $product = $product_result->fetch_assoc();
    $seller_id = $product['seller_id'];

    $seller_stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $seller_stmt->bind_param("i", $seller_id);
    $seller_stmt->execute();
    $seller_result = $seller_stmt->get_result();
    $seller = $seller_result->fetch_assoc();
    $seller_email = $seller['email'];

    // Send email notification to the seller
    $subject = "New Order from " . $full_name;
    $body = "You have received a new order. \n\nOrder Details:\n";
    $body .= "Buyer Name: " . $full_name . "\n";
    $body .= "Product: " . $product['name'] . "\n";
    $body .= "Quantity: " . $quantity . "\n";
    $body .= "Total Price: GH₵" . number_format($total_price, 2) . "\n";
    $body .= "Shipping Address: " . $address . "\n";
    $body .= "Buyer Phone: " . $phone . "\n";
    $body .= "Buyer Email: " . $email . "\n";

    // Send email to the seller
    if (mail($seller_email, $subject, $body)) {
        echo "Order placed successfully!";
    } else {
        echo "Error: Unable to send email notification to the seller.";
    }

    // Redirect or show success message
    header("Location: order_confirmation.php");
    exit();
} else {
    echo "Invalid request.";
}
?>

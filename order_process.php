<?php
session_start();
require 'config.php';

// Get product, buyer, and quantity details
$product_id = $_POST['product_id'];  // The ID of the product
$quantity = $_POST['quantity'];  // Quantity the buyer wants to purchase
$buyer_id = $_SESSION['user_id'];  // Get the buyer's user ID from the session

// Fetch product details to calculate total price
$product_stmt = $conn->prepare("SELECT name, price, seller_id FROM products WHERE id = ?");
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();
$product = $product_result->fetch_assoc();

// Calculate total price
$total_price = $product['price'] * $quantity;

// Insert the order into the orders table
$order_stmt = $conn->prepare("
    INSERT INTO orders (buyer_id, total_price, order_date, status) 
    VALUES (?, ?, NOW(), 'Pending')
");
$order_stmt->bind_param("id", $buyer_id, $total_price);
$order_stmt->execute();
$order_id = $order_stmt->insert_id;  // Get the newly inserted order ID

// Insert the order items (product and quantity)
$order_items_stmt = $conn->prepare("
    INSERT INTO order_items (order_id, product_id, quantity, price) 
    VALUES (?, ?, ?, ?)
");
$order_items_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $total_price);
$order_items_stmt->execute();

// Get seller's email for order notification
$seller_id = $product['seller_id'];
$seller_stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$seller_stmt->bind_param("i", $seller_id);
$seller_stmt->execute();
$seller_result = $seller_stmt->get_result();
$seller = $seller_result->fetch_assoc();
$seller_email = $seller['email'];

// Send email to seller
$subject = "New Order from " . $_SESSION['full_name'];
$body = "You have received a new order. \n\nOrder Details:\n";
$body .= "Buyer Name: " . $_SESSION['full_name'] . "\n";
$body .= "Product: " . $product['name'] . "\n";
$body .= "Quantity: " . $quantity . "\n";
$body .= "Total Price: GH₵" . number_format($total_price, 2) . "\n";

if (mail($seller_email, $subject, $body)) {
    echo "Order placed successfully!";
} else {
    echo "Error: Unable to send email notification to the seller.";
}
?>

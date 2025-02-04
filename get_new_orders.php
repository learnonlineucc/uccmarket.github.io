<?php
session_start();
require 'config.php';

$seller_id = $_SESSION['user_id'];

$order_stmt = $conn->prepare("
    SELECT 
        o.id AS order_id, 
        u.full_name AS buyer_name, 
        oi.quantity, 
        oi.price AS total_price, 
        p.name AS product_name, 
        o.status
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN users u ON o.buyer_id = u.id
    JOIN products p ON oi.product_id = p.id
    WHERE p.seller_id = ?
    ORDER BY o.order_date DESC
");
$order_stmt->bind_param("i", $seller_id);
$order_stmt->execute();
$orders = $order_stmt->get_result();

// Output HTML table rows
while ($row = $orders->fetch_assoc()) {
    echo '<tr>';
    echo '<td>' . $row['buyer_name'] . '</td>';
    echo '<td>' . $row['product_name'] . '</td>';
    echo '<td>' . $row['quantity'] . '</td>';
    echo '<td>GH₵' . number_format($row['total_price'], 2) . '</td>';
    echo '<td>' . $row['status'] . '</td>';
    echo '<td><a href="chat.php?buyer_id=' . $row['buyer_name'] . '" class="btn btn-message">💬 Message Buyer</a></td>';
    echo '</tr>';
}
?>

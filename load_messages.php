<?php
session_start();
require 'config.php';

$buyer_id = $_GET['buyer_id'];
$seller_id = $_GET['seller_id'];

$stmt = $conn->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY sent_at ASC");
$stmt->bind_param("iiii", $buyer_id, $seller_id, $seller_id, $buyer_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $sender = ($row['sender_id'] == $buyer_id) ? "Buyer" : "Seller";
    echo "<div class='message'><strong>$sender:</strong> " . htmlspecialchars($row['message']) . " <small>{$row['sent_at']}</small></div>";
}
?>

<?php
session_start();
require 'config.php';

if (isset($_GET['seller_id']) && isset($_GET['buyer_phone'])) {
    $seller_id = $_GET['seller_id'];
    $buyer_phone = $_GET['buyer_phone'];

    $stmt = $conn->prepare("SELECT * FROM chats WHERE seller_id = ? AND buyer_phone = ? ORDER BY timestamp ASC");
    $stmt->bind_param("is", $seller_id, $buyer_phone);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    // Return messages as JSON
    echo json_encode($messages);
}
?>

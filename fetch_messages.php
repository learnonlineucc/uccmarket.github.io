<?php
session_start();
require 'config.php';

$user_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];

$stmt = $conn->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY sent_at ASC");
$stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $class = ($row['sender_id'] == $user_id) ? 'sent' : 'received';
    echo "<div class='message $class'><p>" . htmlspecialchars($row['message']) . "</p><small>" . $row['sent_at'] . "</small></div>";
}
?>

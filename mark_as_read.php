<?php
// Assuming you have set up PDO connection $pdo

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message_id'])) {
    $message_id = $_POST['message_id'];

    // Mark message as read
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
    $stmt->execute([$message_id]);

    echo "Message marked as read.";
}
?>

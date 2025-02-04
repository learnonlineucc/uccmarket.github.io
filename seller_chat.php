<?php
session_start();
require 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id']; // Logged-in seller ID
$buyer_id = $_GET['buyer_id'] ?? null; // Buyer ID from chat request

if (!$buyer_id) {
    die("<p style='color:red;'>Error: Buyer ID is missing.</p>");
}

// Fetch buyer details
$stmt = $conn->prepare("SELECT full_name, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();
$buyer = $result->fetch_assoc();

$buyer_name = $buyer['full_name'] ?? "Unknown Buyer";
$buyer_phone = $buyer['phone'] ?? "Not available";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($buyer_name); ?></title>
</head>
<body>

<h2>Chat with <?php echo htmlspecialchars($buyer_name); ?> (Buyer)</h2>
<p>Phone: <?php echo htmlspecialchars($buyer_phone); ?></p>

<div id="chat-box"></div>

<input type="text" id="message-input" placeholder="Type your message...">
<button onclick="sendMessage(<?php echo $seller_id; ?>, <?php echo $buyer_id; ?>)">Send</button>

<script>
setInterval(function() { refreshChat(<?php echo $seller_id; ?>, <?php echo $buyer_id; ?>); }, 3000);
</script>

</body>
</html>

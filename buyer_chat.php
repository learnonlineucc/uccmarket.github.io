<?php
session_start();
require 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$buyer_id = $_SESSION['user_id']; // Logged-in buyer ID
$seller_id = $_GET['seller_id'] ?? null; // Seller ID from product page

if (!$seller_id) {
    die("<p style='color:red;'>Error: Seller ID is missing.</p>");
}

// Fetch seller details
$stmt = $conn->prepare("SELECT full_name, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$seller = $result->fetch_assoc();

$seller_name = $seller['full_name'] ?? "Unknown Seller";
$seller_phone = $seller['phone'] ?? "Not available";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($seller_name); ?></title>
    <style>
        .message { padding: 10px; margin: 5px 0; border: 1px solid #ddd; }
        .message p { margin: 5px 0; }
    </style>
</head>
<body>

<h2>Chat with <?php echo htmlspecialchars($seller_name); ?> (Seller)</h2>
<p>Phone: <?php echo htmlspecialchars($seller_phone); ?></p>

<div id="chat-box"></div>

<input type="text" id="message-input" placeholder="Type your message...">
<button onclick="sendMessage(<?php echo $buyer_id; ?>, <?php echo $seller_id; ?>)">Send</button>

<script>
function sendMessage(sender_id, receiver_id) {
    var message = document.getElementById('message-input').value;
    if (message.trim() == "") { alert("Message cannot be empty."); return; }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'send_message.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            refreshChat(sender_id, receiver_id);
            document.getElementById('message-input').value = '';
        }
    };
    xhr.send('message=' + encodeURIComponent(message) + '&sender_id=' + sender_id + '&receiver_id=' + receiver_id);
}

function refreshChat(buyer_id, seller_id) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'load_messages.php?buyer_id=' + buyer_id + '&seller_id=' + seller_id, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            document.getElementById('chat-box').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}

setInterval(function() { refreshChat(<?php echo $buyer_id; ?>, <?php echo $seller_id; ?>); }, 3000);
</script>

</body>
</html>

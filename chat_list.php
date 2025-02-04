<?php
session_start();
require 'config.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

// Handle Chat Deletion Without Redirecting
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_chat'])) {
    $buyer_id = $_POST['buyer_id'];

    $delete_stmt = $conn->prepare("DELETE FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)");
    $delete_stmt->bind_param("iiii", $seller_id, $buyer_id, $buyer_id, $seller_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['chat_delete_success'] = "Chat deleted successfully!";
    } else {
        $_SESSION['chat_delete_error'] = "Failed to delete chat: " . $conn->error;
    }
}

// Fetch all conversations
$stmt = $conn->prepare("
    SELECT DISTINCT m.sender_id, u.full_name AS buyer_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.receiver_id = ?
    ORDER BY m.sent_at DESC
");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$conversations = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat List - UCC Market</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script>
        function deleteChat(buyerId) {
            if (confirm('Are you sure you want to delete this chat?')) {
                document.getElementById('deleteForm' + buyerId).submit();
            }
        }
    </script>
    <style>  <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #000000 50%, #ffffff 50%);
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #3498db;
        }
        .alert {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
    font-weight: bold;
    text-align: center;
}

.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}


        .dashboard-section {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .chat-item {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid #ddd;
            background-color: #f9f9f9;
            border-radius: 5px;
            margin-bottom: 10px;
            transition: background-color 0.3s;
        }

        .chat-item:hover {
            background-color: #f1f1f1;
        }

        .chat-item .buyer-name {
            font-weight: bold;
            color: #3498db;
        }

        .chat-item .latest-message {
            color: #777;
            font-size: 14px;
            flex-grow: 1;
            margin-left: 10px;
        }

        .chat-item .unread-badge {
            background-color: red;
            color: white;
            padding: 5px 10px;
            border-radius: 50%;
            font-size: 12px;
        }

        .btn-chat {
            display: inline-flex;
            align-items: center;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .btn-chat i {
            margin-right: 8px;
        }

        .btn-chat:hover {
            background-color: #0056b3;
        }
        /* Delete Chat Icon */
.btn-delete-chat {
    color: red;
    font-size: 18px;
    margin-left: 15px;
    transition: color 0.3s ease-in-out;
}

.btn-delete-chat:hover {
    color: darkred;
}
.btn-back {
            display: inline-block;
            padding: 10px 15px;
            margin: 15px 0;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            text-align: center;
        }

        .btn-back:hover {
            background-color: #0056b3;
        }


    </style>
</head>
<body>
    <div class="container">
        <h2>Chat List - Messages</h2>

        <!-- Display Messages -->
        <?php if (isset($_SESSION['chat_delete_success'])): ?>
            <div class="alert success">
                <?php echo $_SESSION['chat_delete_success']; unset($_SESSION['chat_delete_success']); ?>
            </div>
            <a href="seller_dashboard.php" class="btn-back">
                <i class="fa fa-arrow-left"></i> Back to Seller Dashboard
            </a>
        <?php endif; ?>

        <?php if (isset($_SESSION['chat_delete_error'])): ?>
            <div class="alert error"><?php echo $_SESSION['chat_delete_error']; unset($_SESSION['chat_delete_error']); ?></div>
        <?php endif; ?>

        <!-- Chat List Section -->
        <div class="dashboard-section">
            <h3>Conversations</h3>
            
            <?php while ($row = $conversations->fetch_assoc()): ?>
                <?php 
                    $buyer_id = $row['sender_id'];
                    $buyer_name = $row['buyer_name'];
                    
                    // Get latest message
                    $message_stmt = $conn->prepare("
                        SELECT message FROM messages 
                        WHERE (receiver_id = ? AND sender_id = ?) 
                        OR (receiver_id = ? AND sender_id = ?) 
                        ORDER BY sent_at DESC LIMIT 1
                    ");
                    $message_stmt->bind_param("iiii", $seller_id, $buyer_id, $buyer_id, $seller_id);
                    $message_stmt->execute();
                    $message_result = $message_stmt->get_result();
                    $message = $message_result->fetch_assoc();
                ?>

                <div class="chat-item">
                    <div>
                        <span class="buyer-name"><?php echo $buyer_name; ?></span>
                        <span class="latest-message"><?php echo $message ? $message['message'] : "No messages yet."; ?></span>
                    </div>
                    <a href="chat.php?receiver_id=<?php echo $buyer_id; ?>" class="btn-chat">
                        <i class="fa fa-comment-dots"></i> View Chat
                    </a>
                    
                    <!-- Delete Chat Button -->
                    <form method="POST" id="deleteForm<?php echo $buyer_id; ?>" style="display:inline;">
                        <input type="hidden" name="buyer_id" value="<?php echo $buyer_id; ?>">
                        <input type="hidden" name="delete_chat" value="1">
                        <button type="button" class="btn-delete-chat" onclick="deleteChat(<?php echo $buyer_id; ?>)">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </div>

            <?php endwhile; ?>
        </div>

        <!-- Back to Seller Dashboard Button -->
        <a href="seller_dashboard.php" class="btn-back">
            <i class="fa fa-arrow-left"></i> Back to Seller Dashboard
        </a>
    </div>

    <style>
    
        .alert.success {
            color: green;
            background: #e7f5e6;
            padding: 10px;
            border-left: 5px solid green;
            margin-bottom: 10px;
        }

        .alert.error {
            color: red;
            background: #f8d7da;
            padding: 10px;
            border-left: 5px solid red;
            margin-bottom: 10px;
        }
    </style>
</body>
</html>

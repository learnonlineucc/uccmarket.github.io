<?php
session_start();
require 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['receiver_id']) ? $_GET['receiver_id'] : null;

// Ensure 'role' session is set
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = "buyer"; // Default to buyer if not set (prevents undefined error)
}

$user_role = $_SESSION['role']; // 'seller' or 'buyer'
$back_url = ($user_role === 'seller') ? 'seller_dashboard.php' : 'buyer_dashboard.php';

// Fetch receiver details
$stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->bind_param("i", $receiver_id);
$stmt->execute();
$result = $stmt->get_result();
$receiver = $result->fetch_assoc();
$receiver_name = $receiver['full_name'] ?? "Unknown User";

// Mark messages as read when the seller views them
$stmt = $conn->prepare("UPDATE messages SET status = 'read' WHERE receiver_id = ? AND sender_id = ?");
$stmt->bind_param("ii", $user_id, $receiver_id);
$stmt->execute();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d); /* Diagonal gradient background */
            color: #fff;
        }

        /* Navbar Styling */
        .navbar {
            background-color: rgba(31, 63, 111, 0.8);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .navbar-brand {
            font-size: 22px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .navbar-nav {
            list-style: none;
            display: flex;
            gap: 20px;
        }

        .nav-item .nav-link {
            font-size: 18px;
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 8px;
            transition: background 0.3s;
        }

        .nav-item .nav-link:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Header styling */
        .header {
            background: #002147;
            color: #ffcc00;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }

        /* Chat container */
        .chat-container {
            width: 90%;
            max-width: 1000px;
            height: 70vh;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
            display: flex;
            flex-direction: column;
        }

        /* Chat header */
        .chat-header {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            color: #ffcc00;
            padding: 10px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        /* Chat box */
        .chat-box {
            flex-grow: 1;
            max-height: 50vh;
            overflow-y: auto;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* Message styling */
        .message {
            padding: 12px;
            margin: 5px 0;
            border-radius: 8px;
            max-width: 75%;
            word-wrap: break-word;
            font-size: 15px;
            display: inline-block;
        }

        .sent {
            align-self: flex-end;
            background: #007bff;
            color: white;
        }

        .received {
            align-self: flex-start;
            background: #28a745;
            color: white;
        }

        /* Input group */
        .input-group {
            display: flex;
            margin-top: 10px;
            background: rgba(255, 255, 255, 0.1);
            padding: 10px;
            border-radius: 10px;
        }

        .input-group input {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .input-group button {
            padding: 12px 18px;
            background: #ffcc00;
            color: black;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-left: 10px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .input-group button:hover {
            background: #ffc107;
        }

       /* Footer Styling */
       .footer {
            background: #1f3f6f;
            color: white;
            text-align: center;
            padding: 20px 15px;
            margin-top: 40px;
            font-size: 18px;
            font-weight: 400;
        }

        /* Back button */
        .back-btn {
            display: block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            color: #ffcc00;
            font-weight: bold;
        }

        .back-btn i {
            margin-right: 5px;
        }
    </style>

    <script>
        function loadMessages() {
            $.ajax({
                url: "fetch_messages.php",
                type: "POST",
                data: { receiver_id: "<?php echo $receiver_id; ?>" },
                success: function(response) {
                    $(".chat-box").html(response);
                    $(".chat-box").scrollTop($(".chat-box")[0].scrollHeight);
                }
            });
        }

        $(document).ready(function() {
            loadMessages();
            setInterval(loadMessages, 3000);

            $("form").submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: "send_message.php",
                    type: "POST",
                    data: $("form").serialize(),
                    success: function(response) {
                        $("input[name='message']").val("");
                        loadMessages();
                    }
                });
            });
        });
    </script>
</head>

<body>
       <!-- Navbar -->
   <nav class="navbar">
        <a class="navbar-brand" href="#"><i class="fas fa-shopping-cart"></i> UCC Market</a>
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="buyer_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_cart.php"><i class="fas fa-box"></i>View Cart</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="profile.php"><i class="fas fa-user"></i> Profile</a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn-danger text-white" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </nav>
    <!-- Header -->
    <div class="header">UCC Market Chat</div>

    <!-- Chat Section -->
    <div class="chat-container">
        <div class="chat-header">
            Chat with <?php echo htmlspecialchars($receiver_name); ?>
        </div>
        <div class="chat-box"></div>

        <!-- Input Area -->
        <form method="POST" class="input-group">
            <input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">
            <input type="text" name="message" placeholder="Type a message..." required>
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
        </form>

        <!-- Back Button -->
        <a href="<?php echo $back_url; ?>" class="back-btn"><i class="fas fa-arrow-left"></i>Back to Dashboard</a>

    </div>    
    <footer class="footer">
        <p><strong>Our Mission:</strong> Empowering UCC students with a seamless marketplace to buy, sell, and connect.</p>
    </footer>

</body>
</html>

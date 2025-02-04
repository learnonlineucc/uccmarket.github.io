
<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];





// Fetch messages from the database (adjust the query as needed)
$stmt = $conn->prepare("
    SELECT messages.*, users.full_name 
    FROM messages
    LEFT JOIN users ON users.id = messages.sender_id 
    WHERE receiver_id = ? OR sender_id = ? 
    ORDER BY sent_at DESC
");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result(); // Fetch the results

// Check if query execution was successful
if ($result === false) {
    echo "<p class='error'>Error fetching messages.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
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

        /* Chat notification */
        .notification {
            position: relative;
            cursor: pointer;
        }

        .notification .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: red;
            color: white;
            padding: 5px;
            border-radius: 50%;
            font-size: 12px;
        }


        .btn-danger {
            background-color: #dc3545;
            padding: 8px 15px;
            border-radius: 8px;
            transition: background 0.3s;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

      
/* Chat Container */
.chat-container {
    width: 80%; /* Makes it wider */
    max-width: 1000px; /* Ensures it doesn’t become too wide */
    margin: 80px auto 60px; /* Centers it between header and footer */
    padding: 20px;
    background: rgba(0, 0, 0, 0.7); /* Glass-like effect */
    backdrop-filter: blur(10px);
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    text-align: center;
}

/* Chat Box */
.chat-box {
    max-height: 450px;
    overflow-y: auto;
    padding: 15px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Messages */
.message {
    padding: 15px;
    margin: 10px 0;
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    position: relative;
    color: white;
}


        .sent { background: #007bff; text-align: right; }
        .received { background: #28a745; }

        .delete-btn {
            background: none;
            border: none;
            color: red;
            font-size: 18px;
            cursor: pointer;
        }

.sent {
    background: #007bff;
    text-align: right;
    align-self: flex-end;
}

.received {
    background: #28a745;
    align-self: flex-start;
}

.message strong {
    font-weight: bold;
}

.message small {
    font-size: 12px;
    color: #ccc;
    margin-top: 5px;
}

/* Input Area */
.input-group {
    display: flex;
    margin-top: 15px;
}

.input-group input {
    flex: 1;
    padding: 12px;
    border-radius: 5px;
    border: none;
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.input-group button {
    padding: 12px;
    background: #f1c40f;
    color: black;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    margin-left: 10px;
}

.input-group button:hover {
    background: #f39c12;
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
    </style>
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
  <!-- Chat Section -->
  <div class="chat-container">
        <h2>Chat</h2>
               <div class="chat-box">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="message <?php echo ($row['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                    <div>
                        <strong><?php echo htmlspecialchars($row['full_name']); ?>:</strong>
                        <p><?php echo htmlspecialchars($row['message']); ?></p>
                        <small><?php echo $row['sent_at']; ?></small>
                    </div>

                    <!-- Delete button -->
                    <form method="POST" action="delete_message.php">
                        <input type="hidden" name="message_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="delete-btn"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            <?php } ?>
        </div>
        <!-- Input Area -->
        <form method="POST" class="input-group">
            <input type="text" name="message" placeholder="Type a message..." required>
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
        </form>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p><strong>Our Mission:</strong> Empowering UCC students with a seamless marketplace to buy, sell, and connect.</p>
    </footer>

    <script>
        // Toggle password visibility
        document.querySelector('.toggle-password').addEventListener('click', function () {
            var passwordField = document.getElementById('password');
            var type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>



</body>
</html>

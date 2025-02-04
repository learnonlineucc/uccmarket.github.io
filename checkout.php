<?php
include('config.php');
session_start();

// Check if user is logged in and is a buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in buyer's user ID

// Fetch cart items for the buyer
$sql = "SELECT cart.id, cart.product_id, products.name, cart.quantity, products.price, products.seller_id
        FROM cart
        JOIN products ON cart.product_id = products.id
        WHERE cart.buyer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

$total_price = 0;
$items = [];

while ($item = $cart_items->fetch_assoc()) {
    $total_price += $item['quantity'] * $item['price'];
    $items[] = $item;
}

// Process the order when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $delivery_location = trim($_POST['location']);

    if (empty($delivery_location)) {
        echo "<script>alert('Please enter your delivery location.');</script>";
    } else {
        foreach ($items as $item) {
            $insert_order = "INSERT INTO orders (buyer_id, seller_id, product_id, quantity, total_price, delivery_location, status)
                             VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
            $order_stmt = $conn->prepare($insert_order);
            $order_stmt->bind_param("iiidis", $user_id, $item['seller_id'], $item['product_id'], $item['quantity'], $total_price, $delivery_location);
            $order_stmt->execute();
        }

        // Clear the cart after successful order
        $delete_cart = "DELETE FROM cart WHERE buyer_id = ?";
        $delete_stmt = $conn->prepare($delete_cart);
        $delete_stmt->bind_param("i", $user_id);
        $delete_stmt->execute();

        echo "<script>alert('Order placed successfully!'); window.location.href='buyer_dashboard.php';</script>";
    }
}
// Fetch unread messages count for notifications
$query = "SELECT COUNT(*) as unread_count FROM messages WHERE receiver_id = ? AND is_read = 0";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$unread_messages = $row['unread_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

    <title>Checkout</title>
    <style>
               body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d); /* Diagonal gradient background */
            color: #fff;
            text-align: center;
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

        .container {
            background-color: rgba(0, 0, 0, 0.5); /* Glass-like effect */
            backdrop-filter: blur(10px);            padding: 20px;
            border-radius: 10px;
            display: inline-block;
            width: 50%;
        }
        input, button {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: none;
        }
        button {
            background: green;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background: darkgreen;
        }
          /* Footer */
          .footer {
            background: rgba(31, 63, 111, 0.8);
            color: white;
            text-align: center;
            padding: 20px 15px;
            font-size: 18px;
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
            <a class="nav-link" href="view_cart.php"><i class="fas fa-box"></i> View Cart</a>
        </li>
        <li class="nav-item">
            <a class="nav-link notification" href="messages.php"><i class="fas fa-envelope"></i> Messages
                <?php if ($unread_messages > 0): ?>
                    <span class="badge"><?php echo $unread_messages; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="profile.php"><i class="fas fa-user"></i> Profile</a>
        </li>
        <li class="nav-item">
            <a class="nav-link btn-danger text-white" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </li>
    </ul>
</nav>

    <div class="container">
        <h2>Checkout</h2>
        <p>Total Amount: GH₵<?php echo number_format($total_price, 2); ?></p>
        <form method="POST">
            <input type="text" name="location" placeholder="Enter your delivery location" required>
            <button type="submit">Confirm Order</button>
        </form>
    </div>
    <!-- Footer -->
<div class="footer">
    <p>Our mission is to provide a seamless, trusted marketplace for UCC students, ensuring easy access to quality products at the best prices.</p>
</div>

</body>
</html>

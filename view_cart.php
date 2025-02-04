<?php
include('config.php');  // Include the database connection file
session_start();

// Check if user is logged in and is a buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in buyer's user_id

// Query to fetch cart details with product name, image, price, and quantity
$sql = "SELECT cart.id, products.name, products.image, cart.quantity, products.price
        FROM cart
        JOIN products ON cart.product_id = products.id
        WHERE cart.buyer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

// Debugging the fetched data
if ($cart_items->num_rows > 0) {
    echo "Found items in the cart.<br>";
} else {
    echo "No items found in the cart.<br>";
}

$total_value = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Cart</title>
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

        /* View Cart Styling */
        .cart-container {
            padding: 40px;
        }

        .cart-container h2 {
            font-size: 36px;
            color: #fff;
            margin-bottom: 20px;
        }

        .cart-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
        }

        .cart-item img {
            max-width: 100px;
            border-radius: 8px;
        }

        .cart-item-details {
            flex-grow: 1;
            margin-left: 15px;
        }

        .cart-item-details h3 {
            margin: 0;
            color: #fff;
        }

        .cart-item-details p {
            color: #bbb;
        }

        .cart-item-price {
            color: #fff;
            font-size: 18px;
        }

        .cart-item-actions button {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .cart-item-actions button:hover {
            background-color: #1f3f6f;
        }

        .checkout-btn {
            background-color: #28a745;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 18px;
            border: none;
            margin-top: 30px;
            cursor: pointer;
        }

        .checkout-btn:hover {
            background-color: #218838;
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


   
    <div class="cart-container">
        <h2>Your Cart</h2>

        <?php if ($cart_items->num_rows > 0): ?>
            <?php while ($item = $cart_items->fetch_assoc()): ?>
                <!-- Display the product image -->
                <?php
                    $imagePath = htmlspecialchars($item['image']);

                    // Only prepend "uploads/" if it's not already present in the image path
                    if (strpos($imagePath, 'uploads/') === false) {
                        $imagePath = 'uploads/' . $imagePath;
                    }
                ?>

                <div class="cart-item">
                    <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" />
                    <div class="cart-item-details">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p>Quantity: <?php echo $item['quantity']; ?></p>
                        <p>Price: GH₵<?php echo number_format($item['price'], 2); ?></p>
                    </div>
                    <div class="cart-item-price">
                        GH₵<?php echo number_format($item['quantity'] * $item['price'], 2); ?>
                    </div>
                    <div class="cart-item-actions">
                        <a href="remove_from_cart.php?cart_id=<?php echo $item['id']; ?>"><button>Remove</button></a>
                    </div>
                </div>

                <?php
                    // Update the total value of the cart
                    $total_value += $item['quantity'] * $item['price'];
                ?>
            <?php endwhile; ?>

            <div class="checkout-section">
                <h3>Total: GH₵<?php echo number_format($total_value, 2); ?></h3>
                <a href="checkout.php"><button class="checkout-btn">Proceed to Checkout</button></a>
            </div>
        <?php else: ?>
            <p>Your cart is empty. Browse products to add items.</p>
        <?php endif; ?>
    </div>
    <!-- Footer -->
    <div class="footer">
        <p>Our mission is to provide a seamless, trusted marketplace for UCC students, ensuring easy access to quality products at the best prices.</p>
    </div>
</body>
</html>

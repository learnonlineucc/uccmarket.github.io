<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect if the user is not logged in
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user ID

// Fetch cart items for the user
$query = "SELECT cart.id AS cart_id, products.name, products.image, products.price, cart.quantity
          FROM cart
          JOIN products ON cart.product_id = products.id
          WHERE cart.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>Your cart is empty!</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="cart-container">
        <h2>Your Cart</h2>
        <table>
            <tr>
                <th>Product</th>
                <th>Image</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
            </tr>

            <?php while ($cart_item = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $cart_item['name']; ?></td>
                    <td><img src="<?php echo $cart_item['image']; ?>" alt="<?php echo $cart_item['name']; ?>" class="cart-product-image"></td>
                    <td>GH₵<?php echo number_format($cart_item['price'], 2); ?></td>
                    <td>
                        <form method="POST" action="update_cart.php">
                            <input type="number" name="quantity" value="<?php echo $cart_item['quantity']; ?>" min="1" max="10">
                            <input type="hidden" name="cart_id" value="<?php echo $cart_item['cart_id']; ?>">
                            <button type="submit">Update</button>
                        </form>
                    </td>
                    <td>GH₵<?php echo number_format($cart_item['price'] * $cart_item['quantity'], 2); ?></td>
                    <td>
                        <a href="remove_from_cart.php?cart_id=<?php echo $cart_item['cart_id']; ?>" class="btn btn-remove">Remove</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <a href="checkout.php" class="btn btn-checkout">Proceed to Checkout</a>
    </div>
</body>
</html>

<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['cart_id'])) {
    header("Location: cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_id = $_POST['cart_id'];
$quantity = $_POST['quantity'];

// Update quantity in the cart
$query = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $quantity, $cart_id, $user_id);
$stmt->execute();

header("Location: cart.php");
exit();
?>

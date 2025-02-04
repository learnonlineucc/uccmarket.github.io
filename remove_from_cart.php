<?php
include('config.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_id = isset($_GET['cart_id']) ? $_GET['cart_id'] : 0;

if ($cart_id) {
    // Delete the product from the cart
    $sql = "DELETE FROM cart WHERE id = ? AND buyer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
}

header("Location: view_cart.php"); // Redirect back to the cart page
exit();
?>

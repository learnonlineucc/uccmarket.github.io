<?php
session_start();
require 'config.php';

if (!isset($_GET['id'])) {
    header("Location: buyer_dashboard.php");
    exit();
}

$product_id = $_GET['id'];

// Fetch product details
$query = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: buyer_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UCC Market</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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

        .btn-danger {
            border-radius: 5px;
            padding: 8px 15px;
        }
        .product-details-container {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 20px;
    max-width: 600px;
    margin: 40px auto;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    text-align: center;
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.product-details-container h2 {
    font-size: 24px;
    margin-bottom: 15px;
    font-weight: bold;
}

.product-image {
    width: 100%;
    max-height: 300px;
    object-fit: cover;
    border-radius: 10px;
    margin-bottom: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

p {
    font-size: 16px;
    margin: 10px 0;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    font-size: 16px;
    font-weight: bold;
    text-decoration: none;
    border-radius: 8px;
    transition: 0.3s ease-in-out;
}

.btn-add-to-cart {
    background: #28a745;
    color: white;
    margin-right: 10px;
}

.btn-add-to-cart:hover {
    background: #218838;
}

.btn-chat {
    background: #007bff;
    color: white;
}

.btn-chat:hover {
    background: #0056b3;
}

.btn-add {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.4);
    margin-top: 20px;
}

.btn-add:hover {
    background: rgba(255, 255, 255, 0.4);
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
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="#"><i class="fas fa-shopping-cart"></i> UCC Market</a>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="buyer_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_cart.php"><i class="fas fa-box"></i> View Cart</a>
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

    <div class="container product-details-container">
        <h2><?php echo $product['name']; ?></h2>
        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
        <p><?php echo $product['description']; ?></p>
        <p><strong>Price:</strong> GH₵<?php echo number_format($product['price'], 2); ?></p>
        <p><strong>Category:</strong> <?php echo $product['category']; ?></p>
        
        <a href="add_to_cart.php?product_id=<?php echo $product['id']; ?>" class="btn btn-add-to-cart"><i class="fas fa-cart-plus"></i> Add to Cart</a>
        <a href="chat.php?receiver_id=<?php echo $product['seller_id']; ?>" class="btn btn-chat"><i class="fas fa-comments"></i> Chat with Seller</a>
    </div>
    <div class="text-center">
        <a href="buyer_dashboard.php" class="btn btn-add"><i class="fas fa-arrow-left"></i> Dashboard</a>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 UCC Market. All Rights Reserved.</p>
    </footer>
</body>
</html>

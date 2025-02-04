<?php
session_start();
require 'config.php';

// Check if the buyer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']); // Secure conversion

// Fetch buyer's full name safely using prepared statements
$query = "SELECT full_name FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$buyer = $result->fetch_assoc();
$stmt->close();

if (!$buyer) {
    die("User not found. Please contact support.");
}

// Handle product search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$products = [];
$error_message = '';

if (!empty($search)) {
    if (strlen($search) < 3) {
        $error_message = "Search query must be at least 3 characters long.";
    } else {
        // Use prepared statements
        $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ?");
        $searchTerm = "%" . $search . "%";
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $stmt->close();
    }
}

// Fetch all products if no search is performed
if (empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY uploaded_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
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

        /* Dashboard content */
        .dashboard-content {
            padding: 40px;
            background-color: rgba(0, 0, 0, 0.5); /* Glass-like effect */
            backdrop-filter: blur(10px);
            border-radius: 12px;
            margin: 20px;
        }

        .dashboard-content h2 {
            font-size: 28px;
            color: #fff;
            font-weight: 600;
        }

        .search-bar {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }

        .search-bar input[type="text"] {
            padding: 12px;
            width: 75%;
            font-size: 16px;
            margin-right: 15px;
            border: 2px solid #007bff;
            border-radius: 8px;
            transition: border-color 0.3s;
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .search-bar input[type="text"]:focus {
            border-color: #1f3f6f;
            outline: none;
        }

        .search-bar button {
            padding: 12px 25px;
            font-size: 16px;
            background-color: #007bff;
            border: none;
            color: #fff;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-bar button:hover {
            background-color: #1f3f6f;
        }

        .products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .product-card {
            background-color: rgba(255, 255, 255, 0.2); /* Glass-like */
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: transform 0.4s;
        }

        .product-card:hover {
            transform: scale(1.05);
        }

        .product-card img {
            max-width: 100%;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .product-card h3 {
            font-size: 22px;
            margin: 15px 0;
            color: #fff;
        }

        .product-card p {
            font-size: 16px;
            color: #fff;
        }

        .product-card .btn {
            padding: 12px 20px;
            background-color: #007bff;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-size: 18px;
        }

        .product-card .btn:hover {
            background-color: #1f3f6f;
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

    <!-- Dashboard Content -->
    <div class="dashboard-content">
        <h2>Welcome, <?php echo htmlspecialchars($buyer['full_name']); ?>!</h2>
        <h3>Available Products</h3>

       
    <div class="search-bar">
        <form action="buyer_dashboard.php" method="GET" style="width: 100%; display: flex; justify-content: space-between;">
            <input type="text" name="search" placeholder="Search for products" value="<?php echo htmlspecialchars($search); ?>" minlength="3">
            <button type="submit">Search</button>
        </form>
    </div>

        <?php if (!empty($error_message)) echo "<p style='color: red;'>$error_message</p>"; ?>

        <div class="products">
            <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p>GH₵<?php echo number_format($product['price'], 2); ?></p>
                <a href="view_product.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Our mission is to provide a seamless, trusted marketplace for UCC students, ensuring easy access to quality products at the best prices.</p>
    </div>
</body>
</html>

<?php
session_start();
require 'config.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

// Handle product submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $seller_id = $_SESSION['user_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];

    // Image upload handling
    $target_dir = "uploads/";
    $image = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . time() . "_" . $image; // Unique file name

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Insert product into database
        $stmt = $conn->prepare("INSERT INTO products (seller_id, name, description, price, stock, image, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdiss", $seller_id, $name, $description, $price, $stock, $target_file, $category);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Product added successfully!";
            header("Location: seller_dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Error adding product!";
        }
    } else {
        $_SESSION['error'] = "Image upload failed!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.8);
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 40%;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    animation: fadeIn 1s ease-in-out;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            color: white;
            border-radius: 5px;
        }
        .error { background-color: #dc3545; }
        .success { background-color: #28a745; }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        label {
            text-align: left;
            font-weight: bold;
            color: #555;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        a {
            display: inline-block;
            margin-top: 10px;
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add New Product</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <form action="add_product.php" method="POST" enctype="multipart/form-data">
            <label>Product Name:</label>
            <input type="text" name="name" required>
            
            <label>Description:</label>
            <textarea name="description" required></textarea>
            
            <label>Price (GH₵):</label>
            <input type="number" step="0.01" name="price" required>
            
            <label>Stock:</label>
            <input type="number" name="stock" required>
            
            <label>Category:</label>
            <input type="text" name="category" required>
            
            <label>Upload Image:</label>
            <input type="file" name="image" accept="image/*" required>
            
            <button type="submit">Add Product</button>
        </form>

        <a href="seller_dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>

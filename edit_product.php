<?php
session_start();
require 'config.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

// Check if product ID is provided
if (!isset($_GET['id'])) {
    header("Location: seller_dashboard.php");
    exit();
}

$product_id = $_GET['id'];
$seller_id = $_SESSION['user_id'];

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
$stmt->bind_param("ii", $product_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Product not found!";
    header("Location: seller_dashboard.php");
    exit();
}

$product = $result->fetch_assoc();

// Handle product update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];
    $image_path = $product['image']; // Keep old image unless updated

    // Handle new image upload
    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "uploads/";
        $image = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . time() . "_" . $image;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file; // Use new image path
        } else {
            $_SESSION['error'] = "Image upload failed!";
            header("Location: edit_product.php?id=" . $product_id);
            exit();
        }
    }

    // Update product details
    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category=?, image=? WHERE id=? AND seller_id=?");
    $stmt->bind_param("ssdissii", $name, $description, $price, $stock, $category, $image_path, $product_id, $seller_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Product updated successfully!";
        header("Location: seller_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating product!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
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
            width: 40%;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
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
        .image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Product</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <form action="edit_product.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data">
            <label>Product Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            
            <label>Description:</label>
            <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            
            <label>Price (GH₵):</label>
            <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required>
            
            <label>Stock:</label>
            <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required>
            
            <label>Category:</label>
            <input type="text" name="category" value="<?php echo htmlspecialchars($product['category']); ?>" required>
            
            <label>Current Image:</label><br>
            <img src="<?php echo $product['image']; ?>" class="image-preview" alt="Product Image"><br>

            <label>Upload New Image (Optional):</label>
            <input type="file" name="image" accept="image/*">
            
            <button type="submit">Update Product</button>
        </form>

        <a href="seller_dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>

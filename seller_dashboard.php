<?php
session_start();
require 'config.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

// Fetch seller products
$seller_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE seller_id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$products = $stmt->get_result();



// Fetch orders with buyer details
$order_stmt = $conn->prepare("SELECT orders.id, users.full_name AS buyer_name, users.phone, users.email, products.name AS product_name, orders.quantity, orders.total_price, orders.status 
FROM orders 
JOIN users ON orders.buyer_id = users.id 
JOIN products ON orders.product_id = products.id 
WHERE orders.seller_id = ?");
$order_stmt->bind_param("i", $seller_id);
$order_stmt->execute();
$orders = $order_stmt->get_result();
?>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - UCC Market</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #000000 50%, #ffffff 50%);
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8);
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 100%;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    animation: fadeIn 1s ease-in-out;

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #3498db;
        }

        .dashboard-section {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background: #3498db;
            color: white;
        }

        .btn {
            display: inline-block;
            padding: 8px 12px;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-add { background: #28a745; }
        .btn-edit { background: #f39c12; }
        .btn-delete { background: #e74c3c; }
        .btn-message { background: #9b59b6; }

        .btn:hover {
            opacity: 0.8;
        }
        /* Style for Chat Link and Button */
.btn-chat {
    display: inline-flex;
    align-items: center;
    padding: 10px 15px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
    margin: 5px 0;
    transition: background-color 0.3s ease;
}

.btn-chat i {
    margin-right: 8px;
    font-size: 16px;
}

/* Change background color on hover */
.btn-chat:hover {
    background-color: #0056b3;
}

/* Badge Style for Unread Messages */
#unread-count {
    margin-left: 5px;
    background-color: red;
    color: white;
    padding: 2px 6px;
    border-radius: 50%;
    font-size: 12px;
}

/* Add product button style */
.btn-add {
    padding: 10px 20px;
    background-color: #28a745;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin: 5px 0;
    font-size: 14px;
    transition: background-color 0.3s ease;
}

.btn-add:hover {
    background-color: #218838;
}

/* Edit and Delete buttons style */
.btn-edit, .btn-delete {
    padding: 5px 10px;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
    margin: 3px;
}

.btn-edit {
    background-color: #ffc107;
}

.btn-edit:hover {
    background-color: #e0a800;
}

.btn-delete {
    background-color: #dc3545;
}

.btn-delete:hover {
    background-color: #c82333;
}
.logout-link {
    color: #007bff; /* Change this to your desired color */
    font-size: 18px; /* Adjust the size of the icon and text */
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 5px;
}

.logout-link:hover {
    color: #ff4500; /* Hover effect */
}

.logout-link i {
    font-size: 20px; /* Size of the icon */
}
.contact-icon { color: #007bff; cursor: pointer; margin-left: 5px; font-size: 16px; }
        .contact-icon:hover { color: #0056b3; }
/* Delete Button Styling */
.btn-delete {
    display: inline-block;
    padding: 8px 12px;
    background-color: #dc3545; /* Red */
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    transition: background 0.3s ease-in-out;
}

.btn-delete:hover {
    background-color: #c82333; /* Darker red */
}

.btn-delete i {
    margin-right: 5px;
}



    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo $_SESSION['full_name']; ?> (Seller)</h2>

        <!-- Products Section -->
        <div class="dashboard-section">
            <h3>My Products</h3>
             <!-- Chat Link with Icon -->
    <a href="chat_list.php" class="btn btn-chat">
        <i class="fa fa-comments"></i> Chat 
        <span id="unread-count" class="badge badge-danger"></span>
    </a>

            <a href="add_product.php" class="btn btn-add">➕ Add Product</a>
            <a href="logout.php" class="logout-link">
    <i class="fas fa-sign-out-alt"></i> Logout
</a>


            <table>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $products->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td>GH₵<?php echo number_format($row['price'], 2); ?></td>
                    <td><?php echo $row['stock']; ?></td>
                    <td><?php echo $row['category']; ?></td>
                    <td>
                        <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">✏ Edit</a>
                        <a href="delete_product.php?id=<?php echo $row['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure?');">🗑 Delete</a>
                    </td>
                 
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <h2>Orders Received</h2>
    <table>
        <tr>
            <th>Buyer</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Total Price</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $orders->fetch_assoc()): ?>
        <tr>
            <td>
                <?php echo htmlspecialchars($row['buyer_name']); ?>
                <i class="fas fa-info-circle contact-icon" onclick="showContact('<?php echo htmlspecialchars($row['buyer_name']); ?>', '<?php echo htmlspecialchars($row['phone']); ?>', '<?php echo htmlspecialchars($row['email']); ?>')"></i>
            </td>
            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
            <td>GH₵<?php echo number_format($row['total_price'], 2); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td>
    <a href="delete_order.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this order?');">
        <i class="fa fa-trash"></i> Delete Order
    </a>
</td>
        </tr>
        <?php endwhile; ?>
    </table>

    <script>
    function showContact(name, phone, email) {
        alert("Buyer: " + name + "\nPhone: " + phone + "\nEmail: " + email);
    }
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function checkNotifications() {
        $.ajax({
            url: "get_unread_messages.php",
            success: function(data) {
                if (data > 0) {
                    $("#unread-count").text(data);
                } else {
                    $("#unread-count").text("");
                }
            }
        });
    }

    setInterval(checkNotifications, 5000); // Check every 5 seconds
</script>

</body>
</html>

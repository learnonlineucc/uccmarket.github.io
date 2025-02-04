
<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$query = "SELECT full_name, email, phone, level, program FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
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


        .btn-danger {
            background-color: #dc3545;
            padding: 8px 15px;
            border-radius: 8px;
            transition: background 0.3s;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

       /* Profile Container */
       .profile-container {
        padding: 40px;
            background-color: rgba(0, 0, 0, 0.5); /* Glass-like effect */
            backdrop-filter: blur(10px);
            border-radius: 12px;
            margin: 20px;
        }

        .profile-container h2 {
            color: #1f3f6f;
            margin-bottom: 20px;
            text-align: center;
        }

        .profile-form {
            display: flex;
            flex-direction: column;
            max-width: 500px;
            margin: auto;
        }

        .profile-form label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .profile-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .password-container {
            position: relative;
        }

        .password-container input {
            padding-right: 30px;
        }

        .password-container i {
            position: absolute;
            top: 12px;
            right: 10px;
            cursor: pointer;
        }

        .profile-form button {
            background-color: #1f3f6f;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .profile-form button:hover {
            background-color: #16325c;
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

    <!-- Profile Section -->
    <div class="profile-container">
        <h2>Your Profile</h2>
        <form class="profile-form" action="update_profile.php" method="POST">
            <label for="full_name">Full Name</label>
            <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="phone">Phone Number</label>
            <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>

            <label for="level">Level</label>
            <input type="text" name="level" id="level" value="<?php echo htmlspecialchars($user['level']); ?>" required>

            <label for="program">Program</label>
            <input type="text" name="program" id="program" value="<?php echo htmlspecialchars($user['program']); ?>" required>

            <label for="password">Password (Leave blank to keep current)</label>
            <div class="password-container">
                <input type="password" name="password" id="password">
                <i class="fas fa-eye toggle-password"></i>
            </div>

            <button type="submit">Update Profile</button>
        </form>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p><strong>Our Mission:</strong> Empowering UCC students with a seamless marketplace to buy, sell, and connect.</p>
    </footer>

    <script>
        // Toggle password visibility
        document.querySelector('.toggle-password').addEventListener('click', function () {
            var passwordField = document.getElementById('password');
            var type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>



</body>
</html>

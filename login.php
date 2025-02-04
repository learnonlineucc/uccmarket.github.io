<?php
session_start();
require 'config.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($email)) {
        $errors[] = "⚠️ Email is required.";
    }
    if (empty($password)) {
        $errors[] = "⚠️ Password is required.";
    }

    if (empty($errors)) {
        // Check if the user exists in the database
        $stmt = $conn->prepare("SELECT id, full_name, email, password, role, is_verified FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Fetch user data
            $stmt->bind_result($id, $full_name, $db_email, $db_password, $role, $is_verified);
            $stmt->fetch();

            if ($is_verified == 1) {
                // Verify password
                if (password_verify($password, $db_password)) {
                    // Set session variables
                    $_SESSION['user_id'] = $id;
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['email'] = $db_email;
                    $_SESSION['role'] = $role;

                    // Redirect based on role
                    if ($role == "buyer") {
                        header("Location: buyer_dashboard.php");
                    } elseif ($role == "seller") {
                        header("Location: seller_dashboard.php");
                    }
                    exit();
                } else {
                    $errors[] = "❌ Incorrect password.";
                }
            } else {
                $errors[] = "⚠️ Your email is not verified. Please check your email.";
            }
        } else {
            $errors[] = "⚠️ No account found with this email.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UCC Market</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Center form styles */
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #000000 50%, #ffffff 50%);
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Form container styles */
        .form-container {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            animation: fadeIn 1s ease-in-out;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        /* Input field styles */
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 15px 0;
            border: 2px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        input:focus {
            border-color: #3498db;
            outline: none;
        }

        /* Button styles */
        button {
            width: 100%;
            padding: 10px;
            background-color: #3498db;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #2980b9;
        }

        /* Error message styling */
        p {
            color: red;
            text-align: center;
        }

        /* Form spacing */
        form {
            margin-bottom: 20px;
        }

        .link {
            text-align: center;
            margin-top: 10px;
        }

        .link a {
            color: #3498db;
            text-decoration: none;
        }

        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- Display errors if any -->
        <?php
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo "<p>$error</p>";
            }
        }
        ?>

        <h2>Login to UCC Market</h2>
        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="password" name="password" placeholder="Enter your password" required>
            <button type="submit">Login</button>
        </form>

        <div class="link">
            <a href="forgot_password.php">Forgot Password?</a>
            <a href="register.php">Don't have an account? Register Here!</a>
        </div>
    </div>
</body>
</html>

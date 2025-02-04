<?php
session_start();
require 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // If verification code is submitted
    if (isset($_POST['verification_code'])) {
        $entered_code = trim($_POST['verification_code'] ?? '');

        if (empty($entered_code)) {
            $errors[] = "Verification code is required.";
        }

        if (empty($errors)) {
            // Retrieve the session data
            $registration_data = $_SESSION['registration_data'] ?? null;

            if (!$registration_data) {
                header("Location: register.php");
                exit();
            }

            // Check if entered code matches the code from session data
            if ($entered_code == $registration_data['verification_code']) {
                $current_time = date('Y-m-d H:i:s');

                if ($current_time > $registration_data['code_expiration']) {
                    $errors[] = "Verification code has expired. Please register again.";
                } else {
                    // Insert user data into the database and mark as verified
                    $is_verified = 1;

                    // Prepare SQL query to insert data
                    $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, student_id, level, program, role, password, verification_code, code_expiration, is_verified) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    $stmt->bind_param("ssssssssssi", 
                        $registration_data['full_name'], 
                        $registration_data['email'], 
                        $registration_data['phone'], 
                        $registration_data['student_id'], 
                        $registration_data['level'], 
                        $registration_data['program'], 
                        $registration_data['role'], 
                        $registration_data['password'], 
                        $registration_data['verification_code'],  // Bind verification code
                        $registration_data['code_expiration'],   // Bind expiration time
                        $is_verified
                    );

                    if ($stmt->execute()) {
                        // Clear session data after successful registration
                        unset($_SESSION['registration_data']);
                        header("Location: login.php?verified=success");
                        exit();
                    } else {
                        $errors[] = "⚠️ Failed to register the user. Please try again.";
                    }
                    $stmt->close();
                }
            } else {
                $errors[] = "❌ Incorrect verification code.";
            }
        }
    }

    // Resend verification code logic
    if (isset($_POST['resend_code'])) {
        if (!empty($_POST['email'])) {
            $email = trim($_POST['email']);

            $stmt = $conn->prepare("SELECT id, verification_code, code_expiration, is_verified FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($user_id, $verification_code, $code_expiration, $is_verified);
                $stmt->fetch();
                
                if ($is_verified == 1) {
                    $errors[] = "✅ Your email is already verified.";
                } else {
                    $current_time = date('Y-m-d H:i:s');
                    if (strtotime($code_expiration) < strtotime($current_time)) {
                        // Generate a new verification code if expired
                        $new_verification_code = rand(100000, 999999);
                        $new_expiration_time = date("Y-m-d H:i:s", strtotime("+10 minutes"));

                        // Update the verification code and expiration time in the database
                        $update_stmt = $conn->prepare("UPDATE users SET verification_code = ?, code_expiration = ? WHERE email = ?");
                        $update_stmt->bind_param("sss", $new_verification_code, $new_expiration_time, $email);
                        if ($update_stmt->execute()) {
                            // Send the new verification code to the user's email
                            sendVerificationCode($email, $new_verification_code);
                            $errors[] = "✅ A new verification code has been sent to your email.";
                        } else {
                            $errors[] = "⚠️ Failed to update verification code.";
                        }
                        $update_stmt->close();
                    } else {
                        // Send the existing verification code if it's still valid
                        sendVerificationCode($email, $verification_code);
                        $errors[] = "✅ A new verification code has been sent to your email.";
                    }
                }
            } else {
                $errors[] = "⚠️ Email not found. Please register first.";
            }
            $stmt->close();
        } else {
            $errors[] = "⚠️ Please provide your email.";
        }
    }
}

// Send verification code function
function sendVerificationCode($email, $code) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'uccmarket848@gmail.com';
        $mail->Password = 'btur fkly mtpd edmi';  
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  
        $mail->Port = 465;

        $mail->setFrom('uccmarket848@gmail.com', 'UCC Market');
        $mail->addAddress($email);
        $mail->Subject = 'Your Verification Code';
        $mail->Body = "Your verification code is: <b>$code</b>. It will expire in 10 minutes.";

        $mail->send();
    } catch (Exception $e) {
        echo "❌ Email could not be sent. Error: {$mail->ErrorInfo}";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - UCC Market</title>
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
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 15px 0;
            border: 2px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        input[type="text"]:focus {
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

        <!-- Verification Code Form -->
        <h2>Verify Your Email</h2>
        <form id="registerForm" action="verify.php" method="POST">
            <input type="text" name="verification_code" placeholder="Enter Verification Code" required>
            <button type="submit">Verify</button>
        </form>

        <!-- Resend Code Form -->
        <form id="resendForm" action="verify.php" method="POST">
            <input type="text" name="email" placeholder="Enter your email" required>
            <button type="submit" name="resend_code">Resend Code</button>
        </form>

        <div class="link">
            <a href="#">Need Help?</a>
            <a href="register.php">Register Here!</a>

        </div>
    </div>
</body>
</html>

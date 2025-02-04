<?php
session_start();
include('config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['verification_code'])) {
        $entered_code = $_POST['verification_code'];

        if (!isset($_SESSION['email'])) {
            echo '⚠️ Session expired. Please register again.';
            exit;
        }

        $email = $_SESSION['email'];

        // Retrieve stored verification code and expiration time
        $stmt = $conn->prepare("SELECT verification_code, code_expiration FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($stored_code, $expiration);
        $stmt->fetch();

        if ($stmt->num_rows > 0) {
            if ($entered_code == $stored_code && strtotime($expiration) > time()) {
                $updateStmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE email = ?");
                $updateStmt->bind_param("s", $email);
                $updateStmt->execute();
                $updateStmt->close();

                echo '✅ Verification successful! You can now log in.';
            } else {
                echo '❌ Invalid or expired code. Please try again.';
            }
        } else {
            echo '⚠️ Email not found.';
        }

        $stmt->close();
    } elseif (isset($_POST['resend_code']) && isset($_POST['email'])) {
        $email = $_POST['email'];
        resendVerificationCode($email, $conn);
    }
}

function resendVerificationCode($email, $conn) {
    $new_code = rand(100000, 999999);

    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows == 0) {
        echo "⚠️ Email not found. Please register first.";
        return;
    }
    $checkStmt->close();

    $stmt = $conn->prepare("UPDATE users SET verification_code = ?, code_expiration = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE email = ?");
    $stmt->bind_param("is", $new_code, $email);
    
    if ($stmt->execute()) {
        sendVerificationEmail($email, $new_code);
        echo "✅ A new verification code has been sent to your email.";
    } else {
        echo "⚠️ Failed to update verification code. Please try again.";
    }

    $stmt->close();
}

function sendVerificationEmail($email, $code) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'uccmarket848@gmail.com'; // Your Gmail email
        $mail->Password = 'btur fkly mtpd edmi';  // Your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  // Using SSL encryption
        $mail->Port = 465;  // Port 465 for SSL

        $mail->setFrom('your-email@example.com', 'ucc_market');
        $mail->addAddress($email);
        $mail->Subject = 'Your Verification Code';
        $mail->Body = "We appreciate your effort to have an account with ucc_market. Please verify your email. Do Not share your code with anyone. Your verification code is: <b>$code</b>. It will expire in 10 minutes. Thank you.";

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
    <title>Verify Email</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            padding: 50px;
        }
        .verification-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }
        h2 {
            color: #333;
        }
        input {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background: #007BFF;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        p {
            color: #666;
        }
    </style>
</head>
<body>

    <div class="verification-container">
        <h2>Enter Verification Code</h2>
        <form action="verify_email.php" method="POST">
            <input type="text" name="verification_code" required placeholder="Enter verification code">
            <button type="submit">Verify</button>
        </form>

        <h3>Didn't receive a code?</h3>
        <form action="verify_email.php" method="POST">
            <input type="email" name="email" required placeholder="Enter your email">
            <button type="submit" name="resend_code">Resend Code</button>
        </form>

        <p>If you didn't receive the code, click the button above to get a new one.</p>
    </div>

</body>
</html>

<?php
include('config.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Check if email exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows == 0) {
        echo "⚠️ Email not found. Please register first.";
        return;
    }
    $checkStmt->close();

    // Generate new code and update database
    $new_code = rand(100000, 999999);
    $stmt = $conn->prepare("UPDATE users SET verification_code = ?, code_expiration = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE email = ?");
    $stmt->bind_param("is", $new_code, $email);

    if ($stmt->execute()) {
        sendVerificationCode($email, $new_code);
        echo "✅ A new verification code has been sent to your email.";
    } else {
        echo "⚠️ Failed to update verification code. Please try again.";
    }

    $stmt->close();
}

function sendVerificationCode($email, $code) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'uccmarket848@gmail.com'; // Your Gmail email
        $mail->Password = 'btur fkly mtpd edmi';  // Your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS encryption
        $mail->Port = 587; // Port for TLS

        $mail->setFrom('your-email@example.com', 'ucc_market');
        $mail->addAddress($email);
        $mail->Subject = 'Your Verification Code';
        $mail->Body = "Your new verification code is: <b>$code</b>. It will expire in 10 minutes.";

        $mail->send();
    } catch (Exception $e) {
        echo "❌ Email could not be sent. Error: {$mail->ErrorInfo}";
    }
}
?>

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if (isset($_SESSION['registration_data'])) {
    $email = $_SESSION['registration_data']['email'];
    $verification_code = $_SESSION['registration_data']['verification_code'];

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'uccmarket848@gmail.com'; // Your Gmail email
                $mail->Password = 'btur fkly mtpd edmi';  // Your Gmail app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  // Using SSL encryption
                $mail->Port = 465;  // Port 465 for SSL

                $mail->setFrom('uccmarket848@gmail.com', 'UCC Market');
                $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Email Verification - UCC Market";
        $mail->Body = "Click the link to verify your account: <a href='verify.php?code=$verification_code'>Verify Now</a>";

        $mail->send();
        echo "Verification email sent.";
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

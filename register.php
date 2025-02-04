<?php
session_start();
require 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $level = trim($_POST['level'] ?? '');
    $program = trim($_POST['program'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate form data
    if (empty($full_name) || empty($email) || empty($phone) || empty($student_id) || empty($level) || empty($program) || empty($role) || empty($password) || empty($confirm_password)) {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    if (!preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password)) {
        $errors[] = "Password must include at least one uppercase letter and one number.";
    }

    // Check if student ID already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Student ID already registered.";
    }
    $stmt->close();

    // If no errors, generate verification code and store data in session
    if (empty($errors)) {
        // Generate a unique verification code
        $verification_code = rand(100000, 999999);
        $expiration_time = date('Y-m-d H:i:s', strtotime('+10 minutes')); // 10 minutes expiration time

        // Store user data temporarily in session
        $_SESSION['registration_data'] = [
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'student_id' => $student_id,
            'level' => $level,
            'program' => $program,
            'role' => $role,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'verification_code' => $verification_code,
            'code_expiration' => $expiration_time // Store expiration time
        ];

        // Send verification code to the user's email
        sendVerificationCode($email, $verification_code);

        // Redirect to the verification page
        header("Location: verify.php");
        exit();
    }
}

function sendVerificationCode($email, $code) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Gmail SMTP server
        $mail->SMTPAuth = true;
                $mail->Username = 'uccmarket848@gmail.com'; // Your Gmail email
                $mail->Password = 'btur fkly mtpd edmi';  // Your Gmail app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  // Using SSL encryption
                $mail->Port = 465;  // Port 465 for SSL

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
    <div class="register-container">
        <img src="images/register.png" alt="UCC Market Icon" class="form-icon">
        
        <?php if (!empty($errors)) { ?>
            <div class="error-messages">
                <ul>
                    <?php foreach ($errors as $error) { ?>
                        <li><?php echo $error; ?></li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>

        <form id="registerForm" method="POST">
            <div class="step step-1 active">
                <h2>Step 1: Personal Info</h2>
                <input type="text" name="full_name" placeholder="Full Name" value="<?php echo isset($full_name) ? $full_name : ''; ?>" required>
                <input type="email" name="email" placeholder="Email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                <input type="tel" name="phone" placeholder="Phone Number" value="<?php echo isset($phone) ? $phone : ''; ?>" required>
                <button type="button" onclick="nextStep(1)">Next</button>
            </div>

            <div class="step step-2">
                <h2>Step 2: Academic Info</h2>
                <input type="text" name="student_id" placeholder="Student ID" value="<?php echo isset($student_id) ? $student_id : ''; ?>" required>
                <input type="text" name="level" placeholder="Level (e.g., 300)" value="<?php echo isset($level) ? $level : ''; ?>" required>
                <input type="text" name="program" placeholder="Program of Study" value="<?php echo isset($program) ? $program : ''; ?>" required>
                <button type="button" onclick="prevStep(1)">Back</button>
                <button type="button" onclick="nextStep(2)">Next</button>
            </div>

            <div class="step step-3">
                <h2>Step 3: Account Setup</h2>
                <select name="role" required>
                    <option value="buyer" <?php echo (isset($role) && $role == 'buyer') ? 'selected' : ''; ?>>Buyer</option>
                    <option value="seller" <?php echo (isset($role) && $role == 'seller') ? 'selected' : ''; ?>>Seller</option>
                </select>

                <div class="input-group">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <button type="button" class="btn-secondary" onclick="generatePassword()">Generate Password</button>
                    <span class="toggle-password" onclick="togglePassword()">👁️</span>
                </div>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="button" onclick="prevStep(2)">Back</button>
                <button type="button" onclick="nextStep(3)">Next</button>
            </div>

            <div id="reviewSection" class="step step-4" style="display:none;">
                <h2>Review Your Information</h2>
                <p><strong>Full Name:</strong> <span id="reviewFullName"></span></p>
                <p><strong>Email:</strong> <span id="reviewEmail"></span></p>
                <p><strong>Phone:</strong> <span id="reviewPhone"></span></p>
                <p><strong>Student ID:</strong> <span id="reviewStudentId"></span></p>
                <p><strong>Level:</strong> <span id="reviewLevel"></span></p>
                <p><strong>Program:</strong> <span id="reviewProgram"></span></p>
                <p><strong>Role:</strong> <span id="reviewRole"></span></p>
                <button type="submit">Submit</button>
                <button type="button" onclick="goBackToAccountSetup()">Back</button>
            </div>
        </form>
        <div class="link">
            <a href="forgot_password.php">Forgot Password?</a>
            <a href="login.php">Have an account? Login  Here!</a>
        </div>
    </div>

    <script>
    function nextStep(step) {
        if (step === 1 && validateStep1()) {
            document.querySelector('.step-1').style.display = 'none';
            document.querySelector('.step-2').style.display = 'block';
        } else if (step === 2 && validateStep2()) {
            document.querySelector('.step-2').style.display = 'none';
            document.querySelector('.step-3').style.display = 'block';
        } else if (step === 3) {
            showReview();
        }
    }

    function prevStep(step) {
        if (step === 1) {
            document.querySelector('.step-2').style.display = 'none';
            document.querySelector('.step-1').style.display = 'block';
        } else if (step === 2) {
            document.querySelector('.step-3').style.display = 'none';
            document.querySelector('.step-2').style.display = 'block';
        }
    }
// Toggle password visibility
function togglePassword() {
    var passwordField = document.getElementById('password');
    var confirmPasswordField = document.querySelector('[name="confirm_password"]');
    var toggleIcon = document.querySelector('.toggle-password');

    if (passwordField.type === "password") {
        passwordField.type = "text";
        confirmPasswordField.type = "text"; // Make the confirm password field visible as well
        toggleIcon.textContent = "🙈"; // Change icon to indicate visible password
    } else {
        passwordField.type = "password";
        confirmPasswordField.type = "password"; // Hide the confirm password field
        toggleIcon.textContent = "👁️"; // Change icon to indicate hidden password
    }
}

// Generate a random password
function generatePassword() {
    var password = generateRandomPassword(12); // 12 characters password
    document.getElementById('password').value = password;
    document.querySelector('[name="confirm_password"]').value = password; // Automatically set the confirm password to the same value
}

// Function to generate a random password
function generateRandomPassword(length) {
    var charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+";
    var password = "";
    for (var i = 0; i < length; i++) {
        var randomIndex = Math.floor(Math.random() * charset.length);
        password += charset[randomIndex];
    }
    return password;
}

    function showReview() {
        var fullName = document.querySelector('[name="full_name"]').value;
        var email = document.querySelector('[name="email"]').value;
        var phone = document.querySelector('[name="phone"]').value;
        var studentId = document.querySelector('[name="student_id"]').value;
        var level = document.querySelector('[name="level"]').value;
        var program = document.querySelector('[name="program"]').value;
        var role = document.querySelector('[name="role"]').value;

        document.getElementById('reviewFullName').textContent = fullName;
        document.getElementById('reviewEmail').textContent = email;
        document.getElementById('reviewPhone').textContent = phone;
        document.getElementById('reviewStudentId').textContent = studentId;
        document.getElementById('reviewLevel').textContent = level;
        document.getElementById('reviewProgram').textContent = program;
        document.getElementById('reviewRole').textContent = role;

        document.getElementById('reviewSection').style.display = 'block';
        document.querySelector('.step-3').style.display = 'none';
    }

    function goBackToAccountSetup() {
        document.getElementById('reviewSection').style.display = 'none';
        document.querySelector('.step-3').style.display = 'block';
    }

    function validateStep1() {
        var fullName = document.querySelector('[name="full_name"]').value;
        var email = document.querySelector('[name="email"]').value;
        var phone = document.querySelector('[name="phone"]').value;

        if (fullName === '' || email === '' || phone === '') {
            alert("Please fill in all required fields.");
            return false;
        }

        if (!validateEmail(email)) {
            alert("Please enter a valid email.");
            return false;
        }

        return true;
    }

    function validateStep2() {
        var studentId = document.querySelector('[name="student_id"]').value;
        var level = document.querySelector('[name="level"]').value;
        var program = document.querySelector('[name="program"]').value;

        if (studentId === '' || level === '' || program === '') {
            alert("Please fill in all required fields.");
            return false;
        }

        return true;
    }

    function validateEmail(email) {
        var regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return regex.test(email);
    }
</script>

</body>
</html>

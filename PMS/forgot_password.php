<?php
session_start();
require 'dbconn.php';

$error = '';
$success = '';

if (isset($_POST['send_otp'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Check if email exists
    $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Generate OTP
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['reset_email'] = $email;
        $_SESSION['otp_expiry'] = time() + 300; // 5 minutes expiry

        // Send OTP via email
        require 'send_otp.php';
        if (sendOTP($email, $otp)) {
            $success = "OTP sent to your email!";
        } else {
            $error = "Failed to send OTP. Please try again.";
        }
    } else {
        $error = "Email not found in our system!";
    }
}

if (isset($_POST['verify_otp'])) {
    $user_otp = $_POST['otp'];
    
    if (time() > $_SESSION['otp_expiry']) {
        $error = "OTP has expired. Please request a new one.";
    } elseif ($user_otp == $_SESSION['otp']) {
        header("Location: reset_password.php");
        exit();
    } else {
        $error = "Invalid OTP!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - PharmaCare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#e6f7f5',
                            100: '#ccefec',
                            200: '#99dfda',
                            300: '#66cfc7',
                            400: '#33bfb5',
                            500: '#00afa2', // Main primary color
                            600: '#008c82',
                            700: '#006961',
                            800: '#004641',
                            900: '#002320',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-white shadow-md py-4">
        <div class="container mx-auto px-4 md:px-6 flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-capsules text-primary-500 text-2xl mr-2"></i>
                <span class="text-primary-600 font-bold text-xl">PharmaCare</span>
            </div>
            <a href="login.php" class="text-blue-600 hover:text-primary-700 transition">
                <i class="fas fa-sign-in-alt mr-1"></i>
                Back to Login
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex-grow container mx-auto px-4 md:px-6 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-primary-500 py-4 px-6">
                <h1 class="text-white text-xl font-semibold flex items-center">
                    <i class="fas fa-lock mr-2"></i>
                    Password Recovery
                </h1>
            </div>

            <div class="px-6 py-4 space-y-4">
                <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
                    <?= $error ?>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
                    <?= $success ?>
                </div>
                <?php endif; ?>

                <!-- Email Form -->
                <form method="POST" action="">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-envelope mr-1 text-primary-500"></i> Email Address
                            </label>
                            <input type="email" name="email" required
                                class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-primary-300 focus:border-primary-500"
                                placeholder="Enter your registered email">
                        </div>
                        <button type="submit" name="send_otp"
                            class="w-full bg-green-500 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded-md transition">
                            Send OTP
                        </button>
                    </div>
                </form>

                <!-- OTP Verification Form -->
                <?php if (isset($_SESSION['otp'])): ?>
                <form method="POST" action="">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-shield-alt mr-1 text-primary-500"></i> Enter OTP
                            </label>
                            <input type="number" name="otp" required
                                class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-primary-300 focus:border-primary-500"
                                placeholder="Enter 6-digit OTP">
                        </div>
                        <button type="submit" name="verify_otp"
                            class="w-full bg-blue-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md transition">
                            Verify OTP
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-6 shadow-inner mt-8">
        <div class="container mx-auto px-4 text-center text-gray-500 text-sm">
            <p>&copy; 2025 PharmaCare Management System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
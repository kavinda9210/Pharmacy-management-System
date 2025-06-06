<?php
session_start();
require 'dbconn.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Validate password strength
        if (strlen($new_password) < 8 || !preg_match('/[A-Za-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
            $error = "Password must be at least 8 characters with letters and numbers";
        } else {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $email = $_SESSION['reset_email'];
            
            $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param('ss', $password_hash, $email);
            
            if ($stmt->execute()) {
                $success = "Password updated successfully!";
                session_destroy();
            } else {
                $error = "Password reset failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - PharmaCare</title>
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
                            500: '#00afa2',
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
                    Reset Your Password
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

                <form method="POST" action="">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-key mr-1 text-primary-500"></i> New Password
                            </label>
                            <input type="password" name="password" required
                                class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-primary-300 focus:border-primary-500"
                                placeholder="Enter new password">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-key mr-1 text-primary-500"></i> Confirm Password
                            </label>
                            <input type="password" name="confirm_password" required
                                class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-primary-300 focus:border-primary-500"
                                placeholder="Confirm new password">
                        </div>
                        <button type="submit"
                            class="w-full bg-blue-500 hover:bg-green-500 text-white font-bold py-2 px-4 rounded-md transition">
                            Reset Password
                        </button>
                    </div>
                </form>
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
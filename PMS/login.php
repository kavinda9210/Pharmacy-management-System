<?php
session_start();
include 'dbconn.php'; // Ensure this line is present and correct

// Redirect logged-in users
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/adminDashboard.php");
    } else {
        header("Location: pharmacist/Dashboard.php");
    }
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $username = strip_tags($username);
    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

    $errors = [];
    
    if (empty($username)) $errors[] = 'Username is required';
    if (empty($password)) $errors[] = 'Password is required';

    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
        header("Location: login.php");
        exit();
    }

    try {
        $stmt = $mysqli->prepare("
            SELECT user_id, name, role, password 
            FROM users 
            WHERE username = ? AND is_active = 1
        ");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                // Role-based redirection
                if ($user['role'] === 'admin') {
                    header("Location: admin/adminDashboard.php");
                } else {
                    header("Location: pharmacist/Dashboard.php");
                }
                exit();
            }
        }

        $_SESSION['error_message'] = 'Invalid username or password';
        header("Location: login.php");
        exit();

    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error_message'] = 'System error. Please try later.';
        header("Location: login.php");
        exit();
    }
}

// Get messages from session
if (isset($_SESSION['error_message'])) {
    $error_message = htmlspecialchars($_SESSION['error_message']);
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['success_message'])) {
    $success_message = htmlspecialchars($_SESSION['success_message']);
    unset($_SESSION['success_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaCare - Login</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
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
    <nav class="shadow-md py-4" style="background-color: #2c3e50;">
        <div class="container mx-auto px-4 md:px-6 flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-capsules text-2xl mr-2" style="color: #ecf0f1;"></i>
                <a href="index.php">
                    <span class="font-bold text-xl" style="color: #ecf0f1;">PharmaCare</span>
                </a>
            </div>
            <button type="button" onclick="window.location.href='register.php'" class="ml-3" style="background-color: #3498db; color: white; padding: 0.5rem 1rem; border-radius: 0.25rem; display: flex; align-items: center;">
                <i class="fas fa-user-plus mr-1"></i>
                <span>Register</span>
            </button>
                
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex-grow container mx-auto px-4 md:px-6 py-8 flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-primary-500 py-6 px-6 text-center">
                <h1 class="text-white text-2xl font-semibold flex items-center justify-center">
                    <i class="fas fa-lock mr-3"></i>
                    Login to PharmaCare
                </h1>
                <p class="text-primary-100 mt-2">Pharmacy Management System</p>
            </div>

            <!-- Alert Messages -->
            <div id="alerts" class="px-6 py-2">
                <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p><?php echo $error_message; ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p><?php echo $success_message; ?></p>
                </div>
                <?php endif; ?>
            </div>

            <form method="POST" action="" class="px-6 py-8 space-y-6" id="loginForm">
                <!-- Username field -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-1 text-primary-500"></i> Username
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-id-badge text-gray-400"></i>
                        </div>
                        <input type="text" name="username" id="username" required
                            class="w-full pl-10 px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary-300 focus:border-primary-500">
                    </div>
                </div>

                <!-- Password field -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-lock mr-1 text-primary-500"></i> Password
                        </label>
        
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-key text-gray-400"></i>
                        </div>
                        <input type="password" name="password" id="password" required
                            class="w-full pl-10 px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary-300 focus:border-primary-500">
                        <button type="button" id="togglePassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <a href="forgot_password.php" class="text-sm text-primary-600 hover:underline">
                            Forgot password?
                        </a>
                <!-- Remember me checkbox -->
                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember"
                        class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">
                        Remember me
                    </label>
                </div>

                <!-- Submit button -->
                <div>
                    <button type="submit" class="w-full bg-primary-500 hover:bg-primary-600 text-white font-bold py-3 px-4 rounded-md transition duration-200 flex items-center justify-center">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login
                    </button>
                </div>

                <!-- Branch Selection -->
             

                <div class="text-center text-sm text-gray-600">
                    Don't have an account? <a href="register.php" class="text-primary-600 hover:underline">Register here</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-6 shadow-inner mt-8">
        <div class="container mx-auto px-4 text-center text-gray-500 text-sm">
            <p>&copy; 2025 PharmaCare Management System. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Toggle password visibility
        const togglePasswordBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePasswordBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });

        

        // Form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            // You can add client-side validation here if needed
            // For example, checking if fields are not empty
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (username === '' || password === '') {
                e.preventDefault();
                alert('Please enter both username and password');
            }
            
            // You could also add a loading state to the button here
            if (username !== '' && password !== '') {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Logging in...';
                submitBtn.disabled = true;
            }
        });
    </script>
</body>
</html>

<?php
session_start();
require 'dbconn.php';

$error_message = '';
$success_message = '';
$form_data = [];
$upload_dir = 'uploads/'; // Photo storage directory

// Create uploads directory if not exists
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Fetch branches for dropdown
$branches = [];
$branch_query = $mysqli->query("SELECT branch_id, location FROM branches");
if ($branch_query) {
    $branches = $branch_query->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $name = strip_tags(trim($_POST['name'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $contact_number = preg_replace('/[^0-9]/', '', trim($_POST['contact_number'] ?? ''));
    $branch_id = (int)($_POST['branch_id'] ?? 0);
    $username = strip_tags(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';
    $address = strip_tags(trim($_POST['address'] ?? ''));
    $nic = strtoupper(strip_tags(trim($_POST['nic'] ?? '')));
    $role = 'pharmacist';
    $photo = null;

    // Handle file upload
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($_FILES['photo']['tmp_name']);
        $file_size = $_FILES['photo']['size'];
        
        if(!in_array($file_type, $allowed_types)) {
            $errors[] = 'Only JPG, PNG, and GIF files are allowed.';
        } elseif ($file_size > 2097152) { // 2MB
            $errors[] = 'File size must be less than 2MB.';
        } else {
            // Generate unique filename
            $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('profile_') . '.' . $extension;
            $target_path = $upload_dir . $filename;
            
            if(move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
                $photo = $filename;
            } else {
                $errors[] = 'Error uploading photo.';
            }
        }
    }

    // Validate inputs
    $errors = [];

    // Name validation
    if (empty($name)) {
        $errors[] = 'Name is required.';
    } elseif (!preg_match('/^[\p{L} \'-]+$/u', $name)) {
        $errors[] = 'Name contains invalid characters.';
    }

    // Email validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }

    // Contact number validation
    if (empty($contact_number) || strlen($contact_number) !== 10) {
        $errors[] = 'Contact number must be 10 digits.';
    }

    // Branch validation
    $branch_check = $mysqli->prepare("SELECT branch_id FROM branches WHERE branch_id = ?");
    $branch_check->bind_param('i', $branch_id);
    $branch_check->execute();
    if ($branch_check->get_result()->num_rows === 0) {
        $errors[] = 'Invalid branch selection.';
    }

    // Username validation
    if (empty($username) || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores.';
    }

    // Password validation
    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must be at least 8 characters with letters and numbers.';
    }

    // NIC validation
    if (!preg_match('/^([0-9]{9}[VXvx]|[0-9]{12})$/', $nic)) {
        $errors[] = 'Invalid NIC format.';
    }

    // Check for errors
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
        $_SESSION['form_data'] = $_POST;
        header("Location: register.php");
        exit();
    }

    // Check existing users
    $check_query = "SELECT username, email, nic FROM users WHERE username = ? OR email = ? OR nic = ?";
    $stmt = $mysqli->prepare($check_query);
    $stmt->bind_param('sss', $username, $email, $nic);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['username'] === $username) {
            $error = "Username already exists!";
        } elseif ($user['email'] === $email) {
            $error = "Email already exists!";
        } elseif ($user['nic'] === $nic) {
            $error = "NIC already exists!";
        }
        $_SESSION['error_message'] = $error;
        $_SESSION['form_data'] = $_POST;
        header("Location: register.php");
        exit();
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $insert_query = "INSERT INTO users (name, email, contact_number, role, branch_id, username, password, address, nic, photo) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($insert_query);
    $stmt->bind_param('ssssisssss', $name, $email, $contact_number, $role, $branch_id, $username, $password_hash, $address, $nic, $photo);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Registration successful! You can now login.";
    } else {
        $_SESSION['error_message'] = "Registration failed. Please try again.";
    }
    header("Location: register.php");
    exit();
}

// Retrieve messages and form data
if (isset($_SESSION['error_message'])) {
    $error_message = htmlspecialchars($_SESSION['error_message']);
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['success_message'])) {
    $success_message = htmlspecialchars($_SESSION['success_message']);
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['form_data'])) {
    $form_data = $_SESSION['form_data'];
    unset($_SESSION['form_data']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaCare - Register</title>
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
    <nav class="shadow-md py-4" style="background-color: #2c3e50;">
        <div class="container mx-auto px-4 md:px-6 flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-capsules text-2xl mr-2" style="color: #ecf0f1;"></i>
                <a href="index.php">
                    <span class="font-bold text-xl" style="color: #ecf0f1;">PharmaCare</span>
                </a>
            </div>
            <button type="button" onclick="window.location.href='login.php'" class="ml-3" style="background-color: #3498db; color: white; padding: 0.5rem 1rem; border-radius: 0.25rem; display: flex; align-items: center;">
                <i class="fas fa-user mr-1"></i>
                <span>Login</span>
            </button>
        </div>
    </nav>

    <div class="flex-grow container mx-auto px-4 md:px-6 py-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-primary-500 py-4 px-6">
                <h1 class="text-white text-xl font-semibold flex items-center">
                    <i class="fas fa-user-plus mr-2"></i>
                    Pharmacist Registration
                </h1>
            </div>

            <div id="alerts" class="px-6 py-2">
                <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p class="font-medium">Error</p>
                    <p><?php echo $error_message; ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p class="font-medium">Success</p>
                    <p><?php echo $success_message; ?></p>
                </div>
                <?php endif; ?>
            </div>

            <form method="POST" action="" class="px-6 py-4 space-y-6" id="registrationForm" enctype="multipart/form-data">
                <!-- Photo Upload -->
                <div>
                    <label for="photo" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-camera mr-1 text-primary-500"></i> Profile Photo
                    </label>
                    <div class="flex items-center space-x-4">
                        <div class="relative group">
                            <div class="w-20 h-20 rounded-full border-2 border-primary-300 overflow-hidden bg-gray-100">
                                <img id="photoPreview" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=" 
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-full flex items-center justify-center cursor-pointer">
                                <i class="fas fa-camera text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <input type="file" name="photo" id="photo" accept="image/*" 
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                            <p class="text-xs text-gray-500 mt-1">JPEG, PNG or GIF (Max 2MB)</p>
                        </div>
                    </div>
                </div>

                <!-- Other Form Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-user mr-1 text-primary-500"></i> Full Name
                        </label>
                        <input type="text" name="name" id="name" required
                            value="<?= htmlspecialchars($form_data['name'] ?? '') ?>" 
                            class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary-300 focus:border-primary-500">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-envelope mr-1 text-primary-500"></i> Email
                        </label>
                        <input type="email" name="email" id="email" required
                            value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" 
                            class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary-300 focus:border-primary-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-phone mr-1 text-primary-500"></i> Contact
                        </label>
                        <input type="text" name="contact_number" id="contact_number" required
                            value="<?= htmlspecialchars($form_data['contact_number'] ?? '') ?>" 
                            class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary-300 focus:border-primary-500">
                    </div>
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-clinic-medical mr-1 text-primary-500"></i> Branch
                        </label>
                        <select name="branch_id" id="branch_id" required class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary-300 focus:border-primary-500">
                            <option value="">Select Branch</option>
                            <?php foreach ($branches as $branch): ?>
                            <option value="<?= $branch['branch_id'] ?>" 
                                <?= (isset($form_data['branch_id']) && $form_data['branch_id'] == $branch['branch_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($branch['location']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-id-badge mr-1 text-primary-500"></i> Username
                        </label>
                        <input type="text" name="username" id="username" required
                            value="<?= htmlspecialchars($form_data['username'] ?? '') ?>" 
                            class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary-300 focus:border-primary-500">
                    </div>
                    <div>
                        <label for="nic" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-id-card mr-1 text-primary-500"></i> NIC
                        </label>
                        <input type="text" name="nic" id="nic" required
                            value="<?= htmlspecialchars($form_data['nic'] ?? '') ?>" 
                            class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary-300 focus:border-primary-500">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-lock mr-1 text-primary-500"></i> Password
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                            class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary-300 focus:border-primary-500">
                        <button type="button" id="togglePassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="mt-2">
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div id="passwordStrength" class="h-full w-0 transition-all duration-300"></div>
                        </div>
                        <p id="passwordFeedback" class="text-xs mt-1 text-gray-500">Password must be at least 8 characters with letters and numbers</p>
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-home mr-1 text-primary-500"></i> Address
                    </label>
                    <textarea name="address" id="address" rows="3"
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary-300 focus:border-primary-500"><?= htmlspecialchars($form_data['address'] ?? '') ?></textarea>
                </div>

                <div class="flex items-start">
                    <input type="checkbox" id="terms" class="mt-1 focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded" required>
                    <label for="terms" class="ml-2 block text-sm text-gray-700">
                        I agree to the <a href="terms.php" class="text-primary-600 hover:underline">Terms and Conditions</a>
                    </label>
                </div>

                <!-- Submit button -->
                <div>
                    <button type="submit" class="w-full bg-primary-500 hover:bg-primary-600 text-white font-bold py-3 px-4 rounded-md transition duration-200 flex items-center justify-center">
                        <i class="fas fa-user-plus mr-2"></i> Register Account
                    </button>
                </div>

                <div class="text-center text-sm text-gray-600">
                    Already have an account? <a href="login.php" class="text-primary-600 hover:underline">Login here</a>
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
        // Add photo preview functionality
        const photoInput = document.getElementById('photo');
        const photoPreview = document.getElementById('photoPreview');
        
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
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

        // Password strength meter
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            const feedback = document.getElementById('passwordFeedback');
            
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 25;
            
            // Contains lowercase
            if (password.match(/[a-z]/)) strength += 25;
            
            // Contains uppercase
            if (password.match(/[A-Z]/)) strength += 25;
            
            // Contains number
            if (password.match(/[0-9]/)) strength += 25;
            
            // Update the strength bar
            strengthBar.style.width = strength + '%';
            
            // Update color based on strength
            if (strength < 50) {
                strengthBar.className = 'h-full bg-red-500';
                feedback.className = 'text-xs mt-1 text-red-500';
                feedback.textContent = 'Weak password. Add letters and numbers.';
            } else if (strength < 75) {
                strengthBar.className = 'h-full bg-yellow-500';
                feedback.className = 'text-xs mt-1 text-yellow-600';
                feedback.textContent = 'Medium strength. Try adding uppercase letters.';
            } else {
                strengthBar.className = 'h-full bg-green-500';
                feedback.className = 'text-xs mt-1 text-green-600';
                feedback.textContent = 'Strong password!';
            }
        });
    </script>
</body>
</html>
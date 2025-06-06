<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require_once "../dbconn.php";

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Store photo in session
$_SESSION['user_photo'] = $user['photo']; // Add this line

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    
    // Handle file upload
    $photo = $user['photo'];
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['photo']['tmp_name']);
        if(!in_array($fileType, $allowedTypes)) {
            $_SESSION['message'] = "Only JPG, PNG, and GIF files are allowed.";
            header("Location: profile.php");
            exit();
        }
        
        // Generate unique filename
        $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('profile_') . '.' . $extension;
        $targetPath = $uploadDir . $filename;
        
        if(move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            if($user['photo'] && file_exists($uploadDir . $user['photo'])) {
                unlink($uploadDir . $user['photo']);
            }
            $photo = $filename;
        } else {
            $_SESSION['message'] = "Error uploading photo.";
            header("Location: profile.php");
            exit();
        }
    }
    
    $update_sql = "UPDATE users SET name=?, email=?, contact_number=?, address=?, photo=? WHERE user_id=?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssssi", $name, $email, $contact_number, $address, $photo, $user_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['user_photo'] = $photo; // Update session photo
        $_SESSION['message'] = "Profile Updated Successfully.";
        header("Location: profile.php");
        exit();
    } else {
        $_SESSION['message'] = "Error updating profile.";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
<body class="bg-gray-100 flex flex-col min-h-screen">
    <div class="flex flex-1">
        <!-- Sidebar -->
        <?php include "include/sidebar.php"; ?>
        
        <!-- Main Content -->
        <main class="flex-1 flex flex-col">
            <!-- Navbar -->
            <?php include "include/navbar.php"; ?>

            <div class="p-6 flex-1">
                <h1 class="text-2xl font-bold text-primary-700 mb-6">Profile</h1>

                <!-- Success & Error Messages -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div id="success-message" class="bg-green-100 border border-green-500 text-green-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($_SESSION['message']) ?>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <!-- Profile Form -->
                <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md">
                    <div class="mb-6 flex items-center gap-6">
                        <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-primary-500">
                            <?php if ($user['photo']): ?>
                                <img src="<?= htmlspecialchars('../uploads/' . $user['photo']) ?>" 
                                     alt="Profile Photo" 
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-gray-500">No photo</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Profile Photo
                            </label>
                            <input type="file" name="photo" 
                                   class="block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-full file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-primary-50 file:text-primary-700
                                          hover:file:bg-primary-100">
                            <p class="text-xs text-gray-500 mt-1">JPEG, PNG or GIF (Max 2MB)</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                        <input type="text" name="contact_number" id="contact_number" value="<?= htmlspecialchars($user['contact_number']) ?>" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" id="address" 
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500" 
                                  required><?= htmlspecialchars($user['address']) ?></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-primary-500 text-white px-4 py-2 rounded-lg hover:bg-primary-600 transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <!-- Footer -->
    <?php include "include/footer.php"; ?>
    <script>
    // Function to remove messages after 3 seconds
    function removeMessages() {
        const successMessage = document.getElementById('success-message');
        if (successMessage) {
            setTimeout(() => {
                successMessage.remove();
            }, 3000);
        }
    }

    // Call the function when the page loads
    window.onload = removeMessages;
    </script>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../dbconn.php';

$message = "";
$user = null;

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $query = "SELECT users.*, branches.location 
              FROM users
              LEFT JOIN branches ON users.branch_id = branches.branch_id
              WHERE users.user_id = $user_id";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $message = "User not found.";
    }
} else {
    $message = "User ID not provided.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = intval($_POST['user_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $role = trim($_POST['role']);
    $branch_id = trim($_POST['branch_id']);
    $username = trim($_POST['username']);
    $address = trim($_POST['address']);
    $nic = trim($_POST['nic']);
    $photo = $_FILES['photo'];

    $photo_path = $user['photo'] ?? null;
    if ($photo['error'] == UPLOAD_ERR_OK) {
        $upload_dir = "../uploads/";  // Changed to parent directory
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $photo_name = basename($photo['name']);
        $unique_name = uniqid() . "_" . $photo_name;
        $filesystem_path = $upload_dir . $unique_name;
        
        if (move_uploaded_file($photo['tmp_name'], $filesystem_path)) {
            $photo_path = "uploads/" . $unique_name;  // Web-accessible path
        } else {
            $message = "File upload failed";
        }
    }

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, contact_number = ?, role = ?, branch_id = ?, username = ?, address = ?, nic = ?, photo = ? WHERE user_id = ?");
    $stmt->bind_param("ssssissssi", $name, $email, $contact_number, $role, $branch_id, $username, $address, $nic, $photo_path, $user_id);

    if ($stmt->execute()) {
        $message = "User Updated Successfully.";
        // Refresh user data after update
        $result = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
        $user = $result->fetch_assoc();
    } else {
        $message = "Failed to update user: " . $stmt->error;
    }

    $stmt->close();
}

$branches = $conn->query("SELECT branch_id, location FROM branches");
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
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
                <h1 class="text-2xl font-bold text-primary-700 mb-6">Edit User</h1>
                <?php if (!empty($message)): ?>
                    <div class="mb-4 p-4 bg-green-200 text-green-800 rounded"> <?= htmlspecialchars($message) ?> </div>
                <?php endif; ?>

                <?php if ($user): ?>
                    <form action="editUser.php" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md">
                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                                required>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                                required>
                        </div>
                        <div class="mb-4">
                            <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                            <input type="text" name="contact_number" id="contact_number" value="<?= htmlspecialchars($user['contact_number']) ?>"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                                required>
                        </div>
                        <div class="mb-4">
                            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                            <select name="role" id="role"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                                required>
                                <option value="pharmacist" <?= $user['role'] == 'pharmacist' ? 'selected' : '' ?>>Pharmacist</option>
                                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch</label>
                            <select name="branch_id" id="branch_id"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                                required>
                                <option value="">Select Branch</option>
                                <?php while ($row = $branches->fetch_assoc()): ?>
                                    <option value="<?= $row['branch_id'] ?>" <?= $row['branch_id'] == $user['branch_id'] ? 'selected' : '' ?>><?= htmlspecialchars($row['location']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                            <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                                required>
                        </div>
                        <div class="mb-4">
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea name="address" id="address"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"><?= htmlspecialchars($user['address']) ?></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="nic" class="block text-sm font-medium text-gray-700">NIC</label>
                            <input type="text" name="nic" id="nic" value="<?= htmlspecialchars($user['nic']) ?>"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                                required>
                        </div>
                        <div class="mb-4">
                            <label for="photo" class="block text-sm font-medium text-gray-700">Photo</label>
                            <input type="file" name="photo" id="photo"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                            <?php if (!empty($user['photo'])): ?>
                                <div class="mt-2">
                                    <img src="../<?= htmlspecialchars($user['photo']) ?>" 
                                         alt="Current Photo" 
                                         class="w-16 h-16 rounded-full object-cover">
                                    <p class="text-sm text-gray-500 mt-1">Current Photo</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg">Update User</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="text-red-500"><?= htmlspecialchars($message) ?></p>
                <?php endif; ?>
            </div>
        </main>
    </div>
                        
                 
    
    <!-- Footer -->
    <?php include "include/footer.php"; ?>
</body>
</html>
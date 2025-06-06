<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../dbconn.php';

$message = "";

// Fetch branches for the dropdown
$branches = $conn->query("SELECT branch_id, location FROM branches");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $role = trim($_POST['role']);
    $branch_id = trim($_POST['branch_id']);
    $password = trim($_POST['password']);
    $username = trim($_POST['username']);
    $address = trim($_POST['address']);
    $nic = trim($_POST['nic']);
    $photo = $_FILES['photo'];

    // Validate required fields
    if (!empty($name) && !empty($email) && !empty($contact_number) && !empty($role) && !empty($branch_id) && !empty($password) && !empty($username) && !empty($nic)) {
        // Check if the email or username already exists
        $check_query = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR username = ?");
        $check_query->bind_param("ss", $email, $username);
        $check_query->execute();
        $check_query->store_result();

        if ($check_query->num_rows > 0) {
            $message = "Email or username already exists.";
        } else {
            // Handle photo upload
            $photo_path = null;
            if ($photo['error'] == UPLOAD_ERR_OK) {
                $upload_dir = "../uploads/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $photo_name = basename($photo['name']);
                $photo_path = $upload_dir . uniqid() . "_" . $photo_name;
                move_uploaded_file($photo['tmp_name'], $photo_path);
            }

            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user
            $stmt = $conn->prepare("INSERT INTO users (name, email, contact_number, role, branch_id, password, username, address, nic, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssisssss", $name, $email, $contact_number, $role, $branch_id, $hashed_password, $username, $address, $nic, $photo_path);

            if ($stmt->execute()) {
                $message = "User added successfully.";
            } else {
                $message = "Failed to add user: " . $stmt->error;
            }

            $stmt->close();
        }
        $check_query->close();
    } else {
        $message = "All fields are required.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
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
                <h1 class="text-2xl font-bold text-primary-700 mb-6">Add User</h1>
                <?php if (!empty($message)): ?>
                    <p class="mb-4 p-4 bg-green-200 text-green-800 rounded"> <?= htmlspecialchars($message) ?> </p>
                <?php endif; ?>

                <form action="addUsers.php" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" id="name"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                        <input type="text" name="contact_number" id="contact_number"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <select name="role" id="role"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                            <option value="pharmacist">Pharmacist</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch</label>
                        <select name="branch_id" id="branch_id"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                            <option value="">Select Branch</option>
                            <?php while ($row = $branches->fetch_assoc()): ?>
                                <option value="<?= $row['branch_id'] ?>"><?= htmlspecialchars($row['location']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" id="password"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="username" id="username"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" id="address"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="nic" class="block text-sm font-medium text-gray-700">NIC</label>
                        <input type="text" name="nic" id="nic"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="photo" class="block text-sm font-medium text-gray-700">Photo</label>
                        <input type="file" name="photo" id="photo"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-primary-500 text-white px-4 py-2 rounded-lg">Add User</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <?php include "include/footer.php"; ?>
</body>

</html>
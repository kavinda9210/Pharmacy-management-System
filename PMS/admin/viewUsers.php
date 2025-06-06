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

if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    
    if ($conn->query("DELETE FROM users WHERE user_id = $user_id")) {
        $message = "User deleted successfully.";
    } else {
        $message = "Failed to delete user: " . $conn->error;
    }
    
    header("Location: viewUsers.php?message=" . urlencode($message));
    exit();
}

$query = "SELECT users.*, branches.location 
          FROM users
          LEFT JOIN branches ON users.branch_id = branches.branch_id
          WHERE users.role = 'pharmacist'";

$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function confirmDelete(userId) {
            if (confirm("Are you sure you want to delete this user?")) {
                window.location.href = "viewUsers.php?delete=" + userId;
            }
        }
        
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
        <?php include "include/sidebar.php"; ?>
        <main class="flex-1 flex flex-col">
            <?php include "include/navbar.php"; ?>

            <div class="p-6 flex-1">
                <h1 class="text-2xl font-bold text-primary-700 mb-6">View Users</h1>
                <?php if (isset($_GET['message'])): ?>
                    <div id="success-message" class="bg-green-100 border border-green-500 text-green-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($_GET['message']) ?>
                    </div>
                <?php endif; ?>

                <table class="w-full bg-white rounded-lg shadow-md overflow-hidden">
                    <thead class="bg-primary-500 text-white">
                        <tr>
                            <th class="p-4">Name</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Contact Number</th>
                            <th class="p-4">Branch</th>
                            <th class="p-4">Username</th>
                            <th class="p-4">NIC</th>
                            <th class="p-4">Photo</th>
                            <th class="p-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="p-4"><?= htmlspecialchars($row['name']) ?></td>
                                <td class="p-4"><?= htmlspecialchars($row['email']) ?></td>
                                <td class="p-4"><?= htmlspecialchars($row['contact_number']) ?></td>
                                <td class="p-4"><?= htmlspecialchars($row['location'] ?? 'N/A') ?></td>
                                <td class="p-4"><?= htmlspecialchars($row['username']) ?></td>
                                <td class="p-4"><?= htmlspecialchars($row['nic']) ?></td>
                                <td class="p-4">
                                    <?php if (!empty($row['photo'])): ?>
                                        <img src="../<?= htmlspecialchars($row['photo']) ?>" 
                                             alt="User Photo" 
                                             class="w-16 h-16 rounded-full object-cover">
                                    <?php else: ?>
                                        <span class="text-gray-500">No Photo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 flex space-x-2">
                                    <a href="editUser.php?id=<?= $row['user_id'] ?>" 
                                       class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition-colors">
                                        Edit
                                    </a>
                                    <button onclick="confirmDelete(<?= $row['user_id'] ?>)" 
                                            class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition-colors">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <?php include "include/footer.php"; ?>
    
    <script>
    function removeMessages() {
        const successMessage = document.getElementById('success-message');
        const errorMessage = document.getElementById('error-message');

        if (successMessage) {
            setTimeout(() => {
                successMessage.remove();
            }, 3000);
        }

        if (errorMessage) {
            setTimeout(() => {
                errorMessage.remove();
            }, 3000);
        }
    }
    window.onload = removeMessages;
    </script>
</body>
</html>

<?php
$conn->close();
?>
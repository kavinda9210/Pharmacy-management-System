<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../dbconn.php';

// Delete supplier
if (isset($_GET['delete'])) {
    $supplier_id = intval($_GET['delete']);
    
    // Prepare delete statement
    $stmt = $conn->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
    $stmt->bind_param("i", $supplier_id);
    
    if ($stmt->execute()) {
        $message = "Supplier Deleted Successfully.";
    } else {
        $message = "Error Deleting Supplier: " . $stmt->error;
    }
    $stmt->close();
    
    header("Location: viewsupplier.php?message=" . urlencode($message));
    exit();
}

// Fetch all suppliers
$query = "SELECT * FROM suppliers ORDER BY created_at DESC";
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
    <title>View Suppliers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function confirmDelete(supplierId) {
            if (confirm("Are you sure you want to delete this supplier?")) {
                window.location.href = "viewsupplier.php?delete=" + supplierId;
            }
        }
    </script>
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
                <h1 class="text-2xl font-bold text-primary-700 mb-6">Manage Suppliers</h1>
                
                <?php if (isset($_GET['message'])): ?>
                    <div id="success-message" class="bg-green-100 border border-green-500 text-green-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($_GET['message']) ?>
                    </div>
                <?php endif; ?>

                <table class="w-full bg-white rounded-lg shadow-md overflow-hidden">
                    <thead class="bg-primary-500 text-white">
                        <tr>
                            <th class="p-4">Supplier Name</th>
                            <th class="p-4">Contact Number</th>
                            <th class="p-4">Location</th>
                            <th class="p-4">Address</th>
                            <th class="p-4">Created At</th>
                            <th class="p-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-4"><?= htmlspecialchars($row['supplier_name']) ?></td>
                                <td class="p-4"><?= htmlspecialchars($row['contact_number']) ?></td>
                                <td class="p-4"><?= htmlspecialchars($row['location']) ?></td>
                                <td class="p-4"><?= htmlspecialchars($row['address']) ?></td>
                                <td class="p-4"><?= date('M j, Y H:i', strtotime($row['created_at'])) ?></td>
                                <td class="p-4">
                                    <div class="flex space-x-2">
                                        <a href="editSupplier.php?id=<?= $row['supplier_id'] ?>" 
                                           class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition-colors">
                                            Edit
                                        </a>
                                        <button onclick="confirmDelete(<?= $row['supplier_id'] ?>)" 
                                                class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition-colors">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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
    window.onload = removeMessages;
    </script>
</body>
</html>

<?php
$conn->close();
?>
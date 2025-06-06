<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../dbconn.php';

$message = '';
$supplier = [];

// Get supplier details
if (isset($_GET['id'])) {
    $supplier_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM suppliers WHERE supplier_id = ?");
    $stmt->bind_param("i", $supplier_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $supplier = $result->fetch_assoc();
    $stmt->close();
    
    if (!$supplier) {
        header("Location: viewsupplier.php?message=Supplier not found");
        exit();
    }
}

// Update supplier
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $supplier_id = intval($_POST['supplier_id']);
    $supplier_name = trim($_POST['supplier_name']);
    $contact_number = trim($_POST['contact_number']);
    $location = trim($_POST['location']);
    $address = trim($_POST['address']);

    $stmt = $conn->prepare("UPDATE suppliers SET 
                            supplier_name = ?, 
                            contact_number = ?, 
                            location = ?, 
                            address = ? 
                            WHERE supplier_id = ?");
    $stmt->bind_param("ssssi", $supplier_name, $contact_number, $location, $address, $supplier_id);

    if ($stmt->execute()) {
        $message = "Supplier Updated Successfully";
    } else {
        $message = "Error updating supplier: " . $stmt->error;
    }
    $stmt->close();
    
    header("Location: viewsupplier.php?message=" . urlencode($message));
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Supplier</title>
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
                <h1 class="text-2xl font-bold text-primary-700 mb-6">Edit Supplier</h1>
                
                <?php if (isset($_GET['message'])): ?>
                    <div id="success-message" class="bg-green-100 border border-green-500 text-green-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($_GET['message']) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="bg-white p-6 rounded-lg shadow-md">
                    <input type="hidden" name="supplier_id" value="<?= $supplier['supplier_id'] ?>">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Supplier Name</label>
                        <input type="text" name="supplier_name" 
                               value="<?= htmlspecialchars($supplier['supplier_name'] ?? '') ?>" 
                               class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-500" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Contact Number</label>
                        <input type="text" name="contact_number" 
                               value="<?= htmlspecialchars($supplier['contact_number'] ?? '') ?>" 
                               class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-500" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Location</label>
                        <input type="text" name="location" 
                               value="<?= htmlspecialchars($supplier['location'] ?? '') ?>" 
                               class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-500" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Address</label>
                        <textarea name="address" 
                                  class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-500" 
                                  required><?= htmlspecialchars($supplier['address'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" 
                            class="bg-green-500 text-white px-4 py-2 rounded hover:bg-primary-600 transition-colors">
                        Update Supplier
                    </button>
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
    window.onload = removeMessages;
    </script>
</body>
</html>
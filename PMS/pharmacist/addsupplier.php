<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../dbconn.php';

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $supplier_name = trim($_POST['supplier_name']);
    $contact_number = trim($_POST['contact_number']);
    $location = trim($_POST['location']);
    $address = trim($_POST['address']);

    if (!empty($supplier_name) && !empty($contact_number) && !empty($location) && !empty($address)) {
        // Check if supplier already exists
        $check_stmt = $conn->prepare("SELECT supplier_id FROM suppliers WHERE supplier_name = ?");
        $check_stmt->bind_param("s", $supplier_name);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error = "Supplier already exists!";
        } else {
            $insert_stmt = $conn->prepare("INSERT INTO suppliers (supplier_name, contact_number, location, address) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssss", $supplier_name, $contact_number, $location, $address);

            if ($insert_stmt->execute()) {
                $success = "Supplier Added Successfully!";
            } else {
                $error = "Error adding supplier: " . $insert_stmt->error;
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    } else {
        $error = "All fields are required!";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Supplier</title>
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
                <h1 class="text-2xl font-bold text-primary-700 mb-6">Add New Supplier</h1>

                <!-- Success & Error Messages -->
                <?php if ($success): ?>
                    <div id="success-message" class="bg-green-100 border border-green-500 text-green-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div id="error-message" class="bg-red-100 border border-red-500 text-red-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Supplier Form -->
                <form method="POST" class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Supplier Name</label>
                        <input type="text" name="supplier_name" required 
                               class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Contact Number</label>
                        <input type="text" name="contact_number" required 
                               class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Location</label>
                        <input type="text" name="location" required 
                               class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Address</label>
                        <textarea name="address" required 
                                  class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>

                    <div class="mb-4">
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-primary-600 transition-colors">
                            Add Supplier
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <script>
    // Function to remove messages after 3 seconds
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

    // Call the function when the page loads
    window.onload = removeMessages;
    </script>

    <!-- Footer -->
    <?php include "include/footer.php"; ?>
</body>
</html>

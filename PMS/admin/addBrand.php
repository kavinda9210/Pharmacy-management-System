
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../dbconn.php';

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure the form field name matches
    if (!empty($_POST['brand_name'])) {
        $brand_name = trim($_POST['brand_name']);

        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO brands (brand_name) VALUES (?)");
        $stmt->bind_param("s", $brand_name); // Only one string parameter

        if ($stmt->execute()) {
            $success = "Brand Added Successfully!";
        } else {
            $error = "Error adding Brand: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $error = "Brand name is required!";
    }
}

$conn->close();
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
                <h1 class="text-2xl font-bold text-primary-700 mb-6">Add New Brand</h1>

                <!-- Success & Error Messages -->
                <?php if ($success): ?>
                    <div id="success-message"
                        class="bg-green-100 border border-green-500 text-green-700 px-4 py-3 rounded mb-4">
                        <?= $success ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div id="error-message" class="bg-red-100 border border-red-500 text-red-700 px-4 py-3 rounded mb-4">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <!-- Branch Form -->
                <form method="POST" class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Brand Name</label>
                        <input type="text" name="brand_name" required class="w-full p-2 border rounded">
                    </div>
                    <div class="mb-4">
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg">Add Brand</button>
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
        const errorMessage = document.getElementById('error-message');

        if (successMessage) {
            setTimeout(() => {
                successMessage.remove();
            }, 3000); // 3000 milliseconds = 3 seconds
        }

        if (errorMessage) {
            setTimeout(() => {
                errorMessage.remove();
            }, 3000); // 3000 milliseconds = 3 seconds
        }
    }

    // Call the function when the page loads
    window.onload = removeMessages;
</script>
</body>
</html>
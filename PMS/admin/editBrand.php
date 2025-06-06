

<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../dbconn.php';

// Get brand details
if (isset($_GET['id'])) {
    $brand_id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM brands WHERE brand_id = $brand_id");
    $brand = $result->fetch_assoc();
}

// Update brand
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $brand_id = intval($_POST['brand_id']);
    $brand_name = $_POST['brand_name'];

    $sql = "UPDATE brands SET brand_name='$brand_name' WHERE brand_id=$brand_id";
    
    if ($conn->query($sql)) {
        $message = "Brand Updated Successfully.";
    } else {
        $message = "Failed to update brand.";
    }
    
    header("Location: viewBrand.php?message=" . urlencode($message));
    exit();
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
                <h1 class="text-2xl font-bold text-primary-700 mb-6">Edit Brand</h1>
                <?php if (isset($_GET['message'])): ?>
    <div id="success-message" class="bg-green-100 border border-green-500 text-green-700 px-4 py-3 rounded mb-4"> <?= htmlspecialchars($_GET['message']) ?>
    </div>
<?php endif; ?>
                <form method="POST" action="editBrand.php" class="bg-white p-6 rounded-lg shadow-md">
                    <input type="hidden" name="brand_id" value="<?= $brand['brand_id'] ?>">
                    <label class="block mb-2">Brand Name</label>
                    <input type="text" name="brand_name" value="<?= htmlspecialchars($brand['brand_name']) ?>" class="w-full p-2 border rounded mb-4" required>
                    
                    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Update Brand</button>
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
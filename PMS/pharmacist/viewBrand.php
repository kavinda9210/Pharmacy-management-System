

<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../dbconn.php';

// Delete brand
if (isset($_GET['delete'])) {
    $brand_name = $conn->real_escape_string($_GET['delete']);
    
    if ($conn->query("DELETE FROM brands WHERE brand_name = '$brand_name'")) {
        $message = "Brand Deleted Successfully.";
    } else {
        $message = "Failed to delete brand.";
    }
    
    header("Location: viewBrand.php?message=" . urlencode($message));
    exit();
}

// Fetch brands
$result = $conn->query("SELECT * FROM brands");
$conn->close();
?>
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function confirmDelete(brandName) {
            if (confirm("Are you sure you want to delete this brand?")) {
                window.location.href = "viewBrand.php?delete=" + encodeURIComponent(brandName);
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
                <h1 class="text-2xl font-bold text-primary-700 mb-6">Manage Brand</h1>
                <?php if (isset($_GET['message'])): ?>
    <div id="success-message" class="bg-green-100 border border-green-500 text-green-700 px-4 py-3 rounded mb-4"> <?= htmlspecialchars($_GET['message']) ?>
    </div>
<?php endif; ?>

                <table class="w-full bg-white rounded-lg shadow-md overflow-hidden">
                    <thead class="bg-primary-500 text-white">
                        <tr>
                            <th class="p-4">Brand Name</th>
                            <th class="p-4">Actions</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="p-4"> <?= htmlspecialchars($row['brand_name']) ?> </td>
                                <td class="p-4">
                                <a href="editBrand.php?id=<?= $row['brand_id'] ?>" class="bg-yellow-500 text-white px-3 py-1 rounded">Edit</a>
                                    <button onclick="confirmDelete('<?= htmlspecialchars($row['brand_name']) ?>')" class="bg-red-500 text-white px-3 py-1 rounded">Delete</button>
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
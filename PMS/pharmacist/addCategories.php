
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../dbconn.php';

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = trim($_POST['category_name']);
    
    if (!empty($category_name)) {
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $category_name);
        
        if ($stmt->execute()) {
            $message = "Category Added Successfully.";
        } else {
            $message = "Failed to add category.";
        }
        
        $stmt->close();
    } else {
        $message = "Category name cannot be empty.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category</title>
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
                <h1 class="text-2xl font-bold text-primary-700 mb-6">Add Category</h1>
                <?php if (!empty($message)): ?>
                    <p class="mb-4 p-4 bg-green-200 text-green-800 rounded"> <?= htmlspecialchars($message) ?> </p>
                <?php endif; ?>

                <form action="addCategories.php" method="POST" class="bg-white p-6 rounded-lg shadow-md">
                    <div class="mb-4">
                        <label for="category_name" class="block text-sm font-medium text-gray-700">Category Name</label>
                        <input type="text" name="category_name" id="category_name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500" required>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg">Add Category</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <!-- Footer -->
    <?php include "include/footer.php"; ?>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../dbconn.php';

$message = "";
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch category details
$category = null;
if ($category_id > 0) {
    $result = $conn->query("SELECT * FROM categories WHERE category_id = $category_id");
    if ($result->num_rows > 0) {
        $category = $result->fetch_assoc();
    } else {
        $message = "Category not found.";
    }
} else {
    $message = "Invalid category ID.";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $category_id > 0) {
    $category_name = trim($_POST['category_name']);
    
    if (!empty($category_name)) {
        $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
        $stmt->bind_param("si", $category_name, $category_id);
        
        if ($stmt->execute()) {
            header("Location: editCategory.php?id=$category_id&message=Category+Updated+Successfully");
            exit();
        } else {
            $message = "Failed to update category.";
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
    <title>Edit Category</title>
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
                <h1 class="text-2xl font-bold text-primary-700 mb-6">Edit Category</h1>
                
                <!-- Success Message from URL -->
                <?php if (isset($_GET['message'])): ?>
                    <div id="success-message" class="bg-green-100 border border-green-500 text-green-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($_GET['message']) ?>
                    </div>
                <?php endif; ?>

                <!-- Error Message from PHP -->
                <?php if (!empty($message)): ?>
                    <div id="error-message" class="bg-red-100 border border-red-500 text-red-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <?php if ($category): ?>
                    <form action="editCategory.php?id=<?= $category_id ?>" method="POST" class="bg-white p-6 rounded-lg shadow-md">
                        <div class="mb-4">
                            <label for="category_name" class="block text-sm font-medium text-gray-700">Category Name</label>
                            <input type="text" name="category_name" id="category_name" value="<?= htmlspecialchars($category['category_name']) ?>" 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500" required>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg">Update Category</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="p-4 bg-red-200 text-red-800 rounded"><?= htmlspecialchars($message) ?></p>
                <?php endif; ?>
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
</body>
</html>
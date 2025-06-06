<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../dbconn.php';

$message = "";

// Fetch data for dropdowns
$suppliers = $conn->query("SELECT supplier_id, supplier_name FROM suppliers");
$categories = $conn->query("SELECT category_id, category_name FROM categories");
$brands = $conn->query("SELECT brand_id, brand_name FROM brands");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $medicine_name = trim($_POST['medicine_name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $quantity = trim($_POST['quantity']);
    $expiry_date = trim($_POST['expiry_date']);
    $supplier_id = trim($_POST['supplier_id']);
    $category_id = trim($_POST['category_id']);
    $brand_id = trim($_POST['brand_id']);

    

    if (!empty($medicine_name)) {
        $stmt = $conn->prepare("INSERT INTO medicines (medicine_name, description, price, quantity, expiry_date, supplier_id, category_id, brand_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdisiii", $medicine_name, $description, $price, $quantity, $expiry_date, $supplier_id, $category_id, $brand_id);

        if ($stmt->execute()) {
            $message = "Medicine added successfully.";
        } else {
            $message = "Failed to add medicine: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $message = "Medicine name cannot be empty.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medicine</title>
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
                <h1 class="text-2xl font-bold text-primary-700 mb-6">Add Medicine</h1>
                <?php if (!empty($message)): ?>
                    <p class="mb-4 p-4 bg-green-200 text-green-800 rounded"> <?= htmlspecialchars($message) ?> </p>
                <?php endif; ?>

                <form action="addMedicines.php" method="POST" class="bg-white p-6 rounded-lg shadow-md">
                    <div class="mb-4">
                        <label for="medicine_name" class="block text-sm font-medium text-gray-700">Medicine Name</label>
                        <input type="text" name="medicine_name" id="medicine_name"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                        <input type="number" step="0.01" name="price" id="price"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                        <input type="number" name="quantity" id="quantity"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="expiry_date" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                        <input type="date" name="expiry_date" id="expiry_date"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required min="<?= date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-4">
                        <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier</label>
                        <select name="supplier_id" id="supplier_id"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                            <option value="">Select Supplier</option>
                            <?php while ($row = $suppliers->fetch_assoc()): ?>
                                <option value="<?= $row['supplier_id'] ?>"><?= htmlspecialchars($row['supplier_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category_id" id="category_id"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                            <option value="">Select Category</option>
                            <?php while ($row = $categories->fetch_assoc()): ?>
                                <option value="<?= $row['category_id'] ?>"><?= htmlspecialchars($row['category_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="brand_id" class="block text-sm font-medium text-gray-700">Brand</label>
                        <select name="brand_id" id="brand_id"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                            <option value="">Select Brand</option>
                            <?php while ($row = $brands->fetch_assoc()): ?>
                                <option value="<?= $row['brand_id'] ?>"><?= htmlspecialchars($row['brand_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg">Add
                            Medicine</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <?php include "include/footer.php"; ?>
</body>

</html>
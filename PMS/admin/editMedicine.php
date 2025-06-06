<?php
session_start(); // Start session
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../dbconn.php';

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$message = "";

// Fetch data for dropdowns
$suppliers = $conn->query("SELECT supplier_id, supplier_name FROM suppliers");
$categories = $conn->query("SELECT category_id, category_name FROM categories");
$brands = $conn->query("SELECT brand_id, brand_name FROM brands");

// Initialize $medicine as null
$medicine = null;

// Fetch medicine details based on ID
if (isset($_GET['id'])) {
    $medicine_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM medicines WHERE medicine_id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error); // Debugging: Output the error
    }
    $stmt->bind_param("i", $medicine_id);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error); // Debugging: Output the error
    }
    $result = $stmt->get_result();
    $medicine = $result->fetch_assoc();
    $stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $medicine_id = intval($_POST['medicine_id']);
    $medicine_name = trim($_POST['medicine_name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $quantity = trim($_POST['quantity']);
    $expiry_date = trim($_POST['expiry_date']);
    $supplier_id = trim($_POST['supplier_id']);
    $category_id = trim($_POST['category_id']);
    $brand_id = trim($_POST['brand_id']);
    $status = trim($_POST['status']);

    if (!empty($medicine_name)) {
        $stmt = $conn->prepare("UPDATE medicines SET medicine_name = ?, description = ?, price = ?, quantity = ?, expiry_date = ?, supplier_id = ?, category_id = ?, brand_id = ?, status = ? WHERE medicine_id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error); // Debugging: Output the error
        }
        $stmt->bind_param("ssdisiiisi", $medicine_name, $description, $price, $quantity, $expiry_date, $supplier_id, $category_id, $brand_id, $status, $medicine_id);
        if ($stmt->execute()) {
            header("Location: editMedicine.php?id=$medicine_id&message=Medicine Updated Successfully.");
            exit();
        }
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
    <title>Edit Medicine</title>
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
                <h1 class="text-2xl font-bold text-primary-700 mb-6">Edit Medicine</h1>
                <?php if (isset($_GET['message'])): ?>
    <div id="success-message" class="bg-green-100 border border-green-500 text-green-700 px-4 py-3 rounded mb-4"> <?= htmlspecialchars($_GET['message']) ?>
    </div>
<?php endif; ?>

                <form action="editMedicine.php" method="POST" class="bg-white p-6 rounded-lg shadow-md">
                    <input type="hidden" name="medicine_id" value="<?= $medicine ? htmlspecialchars($medicine['medicine_id']) : '' ?>">
                    <div class="mb-4">
                        <label for="medicine_name" class="block text-sm font-medium text-gray-700">Medicine Name</label>
                        <input type="text" name="medicine_name" id="medicine_name"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            value="<?= $medicine ? htmlspecialchars($medicine['medicine_name']) : '' ?>" required>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"><?= $medicine ? htmlspecialchars($medicine['description']) : '' ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                        <input type="number" step="0.01" name="price" id="price"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            value="<?= $medicine ? htmlspecialchars($medicine['price']) : '' ?>" required>
                    </div>
                    <div class="mb-4">
                        <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                        <input type="number" name="quantity" id="quantity"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            value="<?= $medicine ? htmlspecialchars($medicine['quantity']) : '' ?>" required>
                    </div>
                    <div class="mb-4">
                        <label for="expiry_date" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                        <input type="date" name="expiry_date" id="expiry_date"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            value="<?= $medicine ? htmlspecialchars($medicine['expiry_date']) : '' ?>" required min="<?= date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-4">
                        <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier</label>
                        <select name="supplier_id" id="supplier_id"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                            <option value="">Select Supplier</option>
                            <?php while ($row = $suppliers->fetch_assoc()): ?>
                                <option value="<?= $row['supplier_id'] ?>" <?= ($medicine && $row['supplier_id'] == $medicine['supplier_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['supplier_name']) ?>
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
                                <option value="<?= $row['category_id'] ?>" <?= ($medicine && $row['category_id'] == $medicine['category_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['category_name']) ?>
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
                                <option value="<?= $row['brand_id'] ?>" <?= ($medicine && $row['brand_id'] == $medicine['brand_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['brand_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                            <option value="in stock" <?= ($medicine && $medicine['status'] == 'in stock') ? 'selected' : '' ?>>In Stock</option>
                            <option value="out of stock" <?= ($medicine && $medicine['status'] == 'out of stock') ? 'selected' : '' ?>>Out of Stock</option>
                        </select>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg">Update
                            Medicine</button>
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
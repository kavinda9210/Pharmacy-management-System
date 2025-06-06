<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
error_reporting(E_ALL); // Enable error reporting
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../dbconn.php';

// Delete medicine
if (isset($_GET['delete'])) {
    $medicine_id = intval($_GET['delete']);
    
    if ($conn->query("DELETE FROM medicines WHERE medicine_id = $medicine_id")) {
        $message = "Medicine Deleted Successfully.";
    } else {
        $message = "Failed to delete medicine.";
    }
    
    header("Location: viewMedicine.php?message=" . urlencode($message));
    exit();
}

// Fetch medicines
$query = "SELECT medicines.*, 
            suppliers.supplier_name, 
            categories.category_name, 
            brands.brand_name 
          FROM medicines
          LEFT JOIN suppliers ON medicines.supplier_id = suppliers.supplier_id
          LEFT JOIN categories ON medicines.category_id = categories.category_id
          LEFT JOIN brands ON medicines.brand_id = brands.brand_id";

$result = $conn->query($query);

// Check for query errors
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Medicines</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function confirmDelete(medicineId) {
            if (confirm("Are you sure you want to delete this medicine?")) {
                window.location.href = "viewMedicine.php?delete=" + medicineId;
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
                <h1 class="text-2xl font-bold text-primary-700 mb-6">Manage Medicines</h1>
                <?php if (isset($_GET['message'])): ?>
    <div id="success-message" class="bg-green-100 border border-green-500 text-green-700 px-4 py-3 rounded mb-4"> <?= htmlspecialchars($_GET['message']) ?>
    </div>
<?php endif; ?>

                <table class="w-full bg-white rounded-lg shadow-md overflow-hidden">
                    <thead class="bg-primary-500 text-white">
                        <tr>
                            <th class="p-4">Name</th>
                            <th class="p-4">Price</th>
                            <th class="p-4">Quantity</th>
                            <th class="p-4">Expiry Date</th>
                            <th class="p-4">Supplier</th>
                            <th class="p-4">Category</th>
                            <th class="p-4">Brand</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="p-4"> <?= htmlspecialchars($row['medicine_name']) ?> </td>
                                <td class="p-4"> $<?= number_format($row['price'], 2) ?> </td>
                                <td class="p-4"> <?= htmlspecialchars($row['quantity']) ?> </td>
                                <td class="p-4"> <?= date('M j, Y', strtotime($row['expiry_date'])) ?> </td>
                                <td class="p-4"> <?= htmlspecialchars($row['supplier_name'] ?? 'N/A') ?> </td>
                                <td class="p-4"> <?= htmlspecialchars($row['category_name'] ?? 'N/A') ?> </td>
                                <td class="p-4"> <?= htmlspecialchars($row['brand_name'] ?? 'N/A') ?> </td>
                                <td class="p-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= $row['status'] == 'in stock' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <a href="editMedicine.php?id=<?= $row['medicine_id'] ?>" class="bg-yellow-500 text-white px-3 py-1 rounded">Edit</a>
                                    <button onclick="confirmDelete(<?= $row['medicine_id'] ?>)" class="bg-red-500 text-white px-3 py-1 rounded">Delete</button>
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

<?php
$conn->close();
?>
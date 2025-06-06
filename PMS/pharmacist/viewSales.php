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

// Delete sale record
if (isset($_GET['delete'])) {
    $sale_id = intval($_GET['delete']);
    
    if ($conn->query("DELETE FROM sales WHERE id = $sale_id")) {
        $message = "Sale Record Deleted Successfully.";
    } else {
        $message = "Failed to delete sale record.";
    }
    
    header("Location: viewSales.php?message=" . urlencode($message));
    exit();
}

// Fetch sales records
$query = "SELECT sales.*, 
            branches.location, 
            medicines.medicine_name 
          FROM sales
          LEFT JOIN branches ON sales.branch_id = branches.branch_id
          LEFT JOIN medicines ON sales.medicine_id = medicines.medicine_id";

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
    <title>View Sales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function confirmDelete(saleId) {
            if (confirm("Are you sure you want to delete this sale record?")) {
                window.location.href = "viewSales.php?delete=" + saleId;
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
                <h1 class="text-2xl font-bold text-primary-700 mb-6">Manage Sales</h1>
                <?php if (isset($_GET['message'])): ?>
                    <div id="success-message" class="bg-green-100 border border-green-500 text-green-700 px-4 py-3 rounded mb-4"> <?= htmlspecialchars($_GET['message']) ?>
                    </div>
                <?php endif; ?>

                <table class="w-full bg-white rounded-lg shadow-md overflow-hidden">
                    <thead class="bg-primary-500 text-white">
                        <tr>
                            <th class="p-4">Location</th>
                            <th class="p-4">Medicine</th>
                            <th class="p-4">Quantity</th>
                            <th class="p-4">Total Price</th>
                            <th class="p-4">Sale Date</th>
                            <th class="p-4">Reference Number</th>
                            <th class="p-4">Customer</th>
                            <th class="p-4">Payment Method</th>
                            <th class="p-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="p-4"> <?= htmlspecialchars($row['location'] ?? 'N/A') ?> </td>
                                <td class="p-4"> <?= htmlspecialchars($row['medicine_name'] ?? 'N/A') ?> </td>
                                <td class="p-4"> <?= htmlspecialchars($row['quantity']) ?> </td>
                                <td class="p-4"> $<?= number_format($row['total_price'], 2) ?> </td>
                                <td class="p-4"> <?= date('M j, Y H:i:s', strtotime($row['sale_date'])) ?> </td>
                                <td class="p-4"> <?= htmlspecialchars($row['reference_number']) ?> </td>
                                <td class="p-4"> <?= htmlspecialchars($row['customer'] ?? 'N/A') ?> </td>
                                <td class="p-4"> <?= htmlspecialchars($row['payment']) ?> </td>
                                <td class="p-4">
                                    <button onclick="confirmDelete(<?= $row['id'] ?>)" class="bg-red-500 text-white px-3 py-1 rounded">Delete</button>
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
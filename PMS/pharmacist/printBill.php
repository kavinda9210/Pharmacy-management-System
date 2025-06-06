<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../dbconn.php';

$reference_number = isset($_GET['reference_number']) ? $_GET['reference_number'] : '';

// Fetch all reference numbers for the autocomplete
$ref_query = "SELECT DISTINCT reference_number FROM sales";
$ref_result = $conn->query($ref_query);
$reference_numbers = [];
while ($ref_row = $ref_result->fetch_assoc()) {
    $reference_numbers[] = $ref_row['reference_number'];
}

// Fetch sale details for the selected reference number
if ($reference_number) {
    $query = "SELECT sales.*, branches.location, medicines.medicine_name 
              FROM sales
              LEFT JOIN branches ON sales.branch_id = branches.branch_id
              LEFT JOIN medicines ON sales.medicine_id = medicines.medicine_id
              WHERE sales.reference_number = '$reference_number'";
    $result = $conn->query($query);

    // Calculate total price
    $total_price = 0;
    while ($row = $result->fetch_assoc()) {
        $total_price += $row['total_price'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Bill</title>
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

<body class="bg-gray-100 p-6">
    <div class="bg-white p-8 rounded-lg shadow-lg border-4 border-primary-500 max-w-2xl mx-auto print-area">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-primary-700">Pharmacy Bill</h1>
            <p class="text-gray-600">Your trusted pharmacy service</p>
        </div>

        <!-- Reference Number Search -->
        <form method="GET" action="" class="mb-8">
            <label for="reference_number" class="block text-lg font-medium text-gray-700 mb-2">Search Reference Number:</label>
            <input type="text" name="reference_number" id="reference_number" value="<?= htmlspecialchars($reference_number) ?>" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md" placeholder="Type to search..." autocomplete="off">
            <div id="suggestions" class="bg-white border border-gray-300 rounded-lg mt-1 hidden"></div>
            <button type="submit" class="mt-4 w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-300">Show Bill</button>
        </form>

        <!-- Bill Details -->
        <?php if ($reference_number && $result->num_rows > 0): ?>
            <div class="bg-gray-50 p-6 rounded-lg border-2 border-gray-200">
                <h2 class="text-2xl font-bold text-primary-700 mb-4 text-center">Bill for Reference: <?= htmlspecialchars($reference_number) ?></h2>
                
                <!-- Fetch the first row to display customer and payment method -->
                <?php
                $result->data_seek(0); // Reset result pointer
                $first_row = $result->fetch_assoc(); // Fetch the first row
                ?>
                
                <!-- Display Customer and Payment Method -->
                <div class="mb-4">
                    <p class="text-lg font-medium text-gray-700">Customer: <span class="font-normal"><?= htmlspecialchars($first_row['customer']) ?></span></p>
                    <p class="text-lg font-medium text-gray-700">Payment Method: <span class="font-normal"><?= htmlspecialchars($first_row['payment']) ?></span></p>
                </div>

                <!-- Display Sale Details -->
                <table class="w-full bg-white rounded-lg shadow-md overflow-hidden">
                    <thead class="bg-primary-500 text-white">
                        <tr>
                            <th class="p-4 text-left">Branch</th>
                            <th class="p-4 text-left">Medicine</th>
                            <th class="p-4 text-left">Quantity</th>
                            <th class="p-4 text-left">Total Price</th>
                            <th class="p-4 text-left">Sale Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result->data_seek(0); // Reset result pointer again for the loop
                        while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b hover:bg-gray-50 transition duration-200">
                                <td class="p-4"><?= htmlspecialchars($row['location']) ?></td>
                                <td class="p-4"><?= htmlspecialchars($row['medicine_name']) ?></td>
                                <td class="p-4"><?= htmlspecialchars($row['quantity']) ?></td>
                                <td class="p-4">$<?= number_format($row['total_price'], 2) ?></td>
                                <td class="p-4"><?= date('M j, Y H:i:s', strtotime($row['sale_date'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="mt-6 text-right">
                    <p class="text-xl font-bold">Total: <span class="text-primary-700">$<?= number_format($total_price, 2) ?></span></p>
                </div>
            </div>
            <div class="mt-6 text-center">
                <button onclick="window.print()" class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-500 transition duration-300">Print Bill</button>
            </div>
        <?php elseif ($reference_number): ?>
            <p class="mt-6 text-red-500 text-center">No records found for the selected reference number.</p>
        <?php endif; ?>
    </div>

    <!-- Back to Dashboard Button -->
    <div class="mt-6 text-center">
        <a href="adminDashboard.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition duration-300">Back to Dashboard</a>
    </div>
</body>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .print-area, .print-area * {
            visibility: visible;
        }
        .print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 0;
            margin: 0;
            box-shadow: none;
            border: none; /* Remove border for print */
        }
        .print-area h1, .print-area h2, .print-area table, .print-area p {
            color: black;
        }
        .print-area button {
            display: none;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const referenceNumberInput = document.getElementById('reference_number');
        const suggestionsDiv = document.getElementById('suggestions');
        const referenceNumbers = <?= json_encode($reference_numbers) ?>;

        referenceNumberInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const filteredSuggestions = referenceNumbers.filter(ref => ref.toLowerCase().includes(searchTerm));

            if (filteredSuggestions.length > 0 && searchTerm !== '') {
                suggestionsDiv.innerHTML = filteredSuggestions.map(ref => `<div class="p-2 hover:bg-gray-100 cursor-pointer">${ref}</div>`).join('');
                suggestionsDiv.classList.remove('hidden');
            } else {
                suggestionsDiv.classList.add('hidden');
            }
        });

        suggestionsDiv.addEventListener('click', function (e) {
            if (e.target.tagName === 'DIV') {
                referenceNumberInput.value = e.target.textContent;
                suggestionsDiv.classList.add('hidden');
            }
        });

        document.addEventListener('click', function (e) {
            if (e.target !== referenceNumberInput) {
                suggestionsDiv.classList.add('hidden');
            }
        });
    });
</script>
</html>
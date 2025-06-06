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
              WHERE sales.reference_number = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $reference_number);
    $stmt->execute();
    $result = $stmt->get_result();

    // Calculate total price
    $total_price = 0;
    $sale_details = [];
    while ($row = $result->fetch_assoc()) {
        $sale_details[] = $row;
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
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
                    },
                    fontFamily: {
                        'sans': ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-100 p-6 font-sans">
    <div class="bg-white p-8 rounded-lg shadow-lg border-t-8 border-primary-500 max-w-3xl mx-auto print-area">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-primary-700">Pharmacy Bill</h1>
            <p class="text-gray-600 italic">Your trusted healthcare partner</p>
            <div class="mt-2 flex justify-center">
                <div class="h-1 w-32 bg-primary-400 rounded"></div>
            </div>
        </div>

        <!-- Reference Number Search -->
        <form method="GET" action="" class="mb-8">
            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                <label for="reference_number" class="block text-lg font-medium text-gray-700 mb-2">
                    <i class="fas fa-search text-primary-500 mr-2"></i>Search Reference Number:
                </label>
                <div class="relative">
                    <input type="text" name="reference_number" id="reference_number" value="<?= htmlspecialchars($reference_number) ?>" 
                           class="mt-1 block w-full pl-4 pr-10 py-3 text-base border-2 border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-lg shadow-sm" 
                           placeholder="Enter reference number..." autocomplete="off">
                    <div id="suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 shadow-lg hidden"></div>
                </div>
                <button type="submit" class="mt-4 w-full bg-primary-600 text-white px-4 py-3 rounded-lg hover:bg-primary-700 transition duration-300 flex items-center justify-center font-medium">
                    <i class="fas fa-receipt mr-2"></i>Generate Bill
                </button>
            </div>
        </form>

        <!-- Bill Details -->
        <?php if (isset($sale_details) && !empty($sale_details)): ?>
            <div class="bg-gray-50 p-6 rounded-lg border-2 border-gray-200 shadow-md">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-primary-700">Bill Details</h2>
                    <span class="bg-primary-100 text-primary-800 px-4 py-1 rounded-full text-sm font-medium">
                        Ref: <?= htmlspecialchars($reference_number) ?>
                    </span>
                </div>
                
                <!-- Customer and Payment Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 bg-white p-4 rounded-lg shadow-sm">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Customer</p>
                        <p class="text-lg font-medium"><?= htmlspecialchars($sale_details[0]['customer']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Payment Method</p>
                        <p class="text-lg font-medium">
                            <?php
                            $payment = htmlspecialchars($sale_details[0]['payment']);
                            $icon = '';
                            if (stripos($payment, 'card') !== false) {
                                $icon = '<i class="fas fa-credit-card mr-2 text-primary-600"></i>';
                            } elseif (stripos($payment, 'cash') !== false) {
                                $icon = '<i class="fas fa-money-bill-wave mr-2 text-green-600"></i>';
                            } else {
                                $icon = '<i class="fas fa-wallet mr-2 text-gray-600"></i>';
                            }
                            echo $icon . $payment;
                            ?>
                        </p>
                    </div>
                </div>

                <!-- Display Sale Details -->
                <div class="overflow-x-auto">
                    <table class="w-full bg-white rounded-lg shadow-md overflow-hidden">
                        <thead class="bg-primary-600 text-white">
                            <tr>
                                <th class="p-4 text-left">Branch</th>
                                <th class="p-4 text-left">Medicine</th>
                                <th class="p-4 text-left">Qty</th>
                                <th class="p-4 text-left">Price</th>
                                <th class="p-4 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sale_details as $index => $row): ?>
                                <tr class="<?= $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' ?> hover:bg-primary-50 transition duration-200">
                                    <td class="p-4 border-t"><?= htmlspecialchars($row['location']) ?></td>
                                    <td class="p-4 border-t font-medium"><?= htmlspecialchars($row['medicine_name']) ?></td>
                                    <td class="p-4 border-t"><?= htmlspecialchars($row['quantity']) ?></td>
                                    <td class="p-4 border-t">Rs.<?= number_format($row['total_price'], 2) ?></td>
                                    <td class="p-4 border-t text-sm text-gray-600"><?= date('M j, Y', strtotime($row['sale_date'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Total Section -->
                <div class="mt-6 bg-white p-4 rounded-lg shadow-sm">
                    <div class="flex justify-between items-center">
                        <p class="text-lg font-medium text-gray-600">Subtotal:</p>
                        <p class="text-lg">Rs.<?= number_format($total_price, 2) ?></p>
                    </div>
                    <div class="border-t border-gray-200 my-2"></div>
                    <div class="flex justify-between items-center">
                        <p class="text-xl font-bold text-gray-800">Total Amount:</p>
                        <p class="text-xl font-bold text-primary-700">Rs.<?= number_format($total_price, 2) ?></p>
                    </div>
                </div>
                
                <!-- Thank You Message -->
                <div class="mt-6 text-center text-gray-600 border-t border-gray-200 pt-4">
                    <p>Thank you for your purchase!</p>
                    <p class="text-sm">For any inquiries, please contact our support team.</p>
                </div>
            </div>
            
            <!-- Print Button -->
            <div class="mt-6 text-center">
                <button onclick="window.print()" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition duration-300 shadow-md flex items-center mx-auto">
                    <i class="fas fa-print mr-2"></i> Print Bill
                </button>
            </div>
        <?php elseif ($reference_number): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-red-700 font-medium">No records found for the selected reference number.</p>
                        <p class="text-red-600 text-sm mt-1">Please check the reference number and try again.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Back to Dashboard Button -->
    <div class="mt-6 text-center">
        <a href="adminDashboard.php" class="bg-gray-600 text-white px-5 py-2 rounded-lg hover:bg-gray-700 transition duration-300 shadow-md flex items-center justify-center w-48 mx-auto">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <!-- Footer -->
    <div class="mt-8 text-center text-gray-500 text-sm">
        <p>&copy; <?= date('Y') ?> Your Pharmacy Name. All rights reserved.</p>
    </div>
</body>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
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
            padding: 20px;
            margin: 0;
            box-shadow: none;
            border: none; /* Remove border for print */
        }
        .print-area button, .print-area form {
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
                suggestionsDiv.innerHTML = filteredSuggestions
                    .map(ref => `<div class="p-3 hover:bg-gray-100 cursor-pointer transition duration-200">${ref}</div>`)
                    .join('');
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
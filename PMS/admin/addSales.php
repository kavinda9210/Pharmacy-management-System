<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../dbconn.php';

$message = "";

// Fetch data for dropdowns
$branches = $conn->query("SELECT branch_id, location FROM branches");
$medicines = $conn->query("SELECT medicine_id, medicine_name, price, quantity FROM medicines");
$reference_numbers = $conn->query("SELECT DISTINCT reference_number FROM sales");

// Fetch all reference numbers for autocomplete
$ref_query = "SELECT DISTINCT reference_number FROM sales";
$ref_result = $conn->query($ref_query);
$reference_numbers_list = [];
while ($ref_row = $ref_result->fetch_assoc()) {
    $reference_numbers_list[] = $ref_row['reference_number'];
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $branch_id = trim($_POST['branch_id']);
    $medicine_id = trim($_POST['medicine_id']);
    $quantity = trim($_POST['quantity']);
    $customer = trim($_POST['customer']); // New field
    $payment = trim($_POST['payment']); // New field
    $reference_number = trim($_POST['reference_number']);

    // Generate a random reference number if "Generate reference" is selected
    if ($reference_number === "generate") {
        $reference_number = "REF" . uniqid(); // Generate a unique reference number
    }

    if (!empty($branch_id) && !empty($medicine_id) && !empty($quantity) && !empty($customer) && !empty($payment)) {
        // Fetch medicine details
        $medicine_query = $conn->query("SELECT price, quantity FROM medicines WHERE medicine_id = $medicine_id");
        $medicine_data = $medicine_query->fetch_assoc();
        $medicine_price = $medicine_data['price'];
        $current_quantity = $medicine_data['quantity'];

        // Check if the medicine is out of stock
        if ($current_quantity <= 0) {
            $message = "Medicine is out of stock.";
        } elseif ($current_quantity < $quantity) {
            $message = "Insufficient quantity in stock.";
        } else {
            // Calculate total price for the current sale
            $total_price = $medicine_price * $quantity;

            // Check if the reference number already exists
            $existing_sale_query = $conn->query("SELECT SUM(total_price) AS total FROM sales WHERE reference_number = '$reference_number'");
            $existing_sale_data = $existing_sale_query->fetch_assoc();
            $existing_total_price = $existing_sale_data['total'] ?? 0;

            // Update the total price
            $new_total_price = $existing_total_price + $total_price;

            // Check if the reference number already exists
            // Insert new sale record
            $stmt = $conn->prepare("INSERT INTO sales (branch_id, medicine_id, quantity, total_price, reference_number, customer, payment) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiidsss", $branch_id, $medicine_id, $quantity, $total_price, $reference_number, $customer, $payment);

            if ($stmt->execute()) {
                // Update medicine quantity
                $new_quantity = $current_quantity - $quantity;
                $conn->query("UPDATE medicines SET quantity = $new_quantity WHERE medicine_id = $medicine_id");

                $message = "Sales Record Added Successfully.";
            } else {
                $message = "Failed to add sale record: " . $stmt->error;
            }

            $stmt->close();
        }
    } else {
        $message = "All fields are required.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Sale</title>
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
    <script>
        // Function to calculate total price dynamically
        function calculateTotalPrice() {
            const medicineId = document.getElementById('medicine_id').value;
            const quantity = document.getElementById('quantity').value;

            if (medicineId && quantity) {
                // Fetch medicine price using AJAX
                fetch(`getMedicinePrice.php?medicine_id=${medicineId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.price) {
                            const totalPrice = data.price * quantity;
                            document.getElementById('total_price').value = totalPrice.toFixed(2);
                        } else {
                            alert("Medicine is out of stock.");
                            document.getElementById('total_price').value = 0;
                        }
                    })
                    .catch(error => console.error('Error fetching medicine price:', error));
            } else {
                document.getElementById('total_price').value = 0;
            }
        }

        // Function to check stock availability
        function checkStock() {
            const medicineId = document.getElementById('medicine_id').value;
            if (medicineId) {
                fetch(`getMedicineStock.php?medicine_id=${medicineId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.quantity <= 0) {
                            alert("Medicine is out of stock.");
                            document.getElementById('quantity').value = 0;
                            document.getElementById('total_price').value = 0;
                        }
                    })
                    .catch(error => console.error('Error fetching medicine stock:', error));
            }
        }

        // Function to generate a random reference number
        function generateReferenceNumber() {
            const referenceNumber = "REF" + Math.random().toString(36).substring(2, 15).toUpperCase();
            document.getElementById('reference_number').value = referenceNumber;
        }

        // Attach event listeners
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('medicine_id').addEventListener('change', checkStock);
            document.getElementById('quantity').addEventListener('input', calculateTotalPrice);

            // Autocomplete for reference number
            const referenceNumberInput = document.getElementById('reference_number');
            const suggestionsDiv = document.getElementById('suggestions');
            const referenceNumbers = <?= json_encode($reference_numbers_list) ?>;

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
                <h1 class="text-2xl font-bold text-primary-700 mb-6">Add Sale</h1>
                <?php if (!empty($message)): ?>
                    <p class="mb-4 p-4 bg-green-200 text-green-800 rounded"> <?= htmlspecialchars($message) ?> </p>
                <?php endif; ?>

                <form action="addSales.php" method="POST" class="bg-white p-6 rounded-lg shadow-md">
                    <div class="mb-4">
                        <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch</label>
                        <select name="branch_id" id="branch_id"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                            <option value="">Select Branch</option>
                            <?php while ($row = $branches->fetch_assoc()): ?>
                                <option value="<?= $row['branch_id'] ?>"><?= htmlspecialchars($row['location']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="medicine_id" class="block text-sm font-medium text-gray-700">Medicine</label>
                        <select name="medicine_id" id="medicine_id"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                            <option value="">Select Medicine</option>
                            <?php while ($row = $medicines->fetch_assoc()): ?>
                                <option value="<?= $row['medicine_id'] ?>"><?= htmlspecialchars($row['medicine_name']) ?> (<?= $row['quantity'] ?> in stock)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                        <input type="number" name="quantity" id="quantity"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="customer" class="block text-sm font-medium text-gray-700">Customer</label>
                        <input type="text" name="customer" id="customer"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            placeholder="Enter customer name" required>
                    </div>
                    <div class="mb-4">
                        <label for="payment" class="block text-sm font-medium text-gray-700">Payment Method</label>
                        <select name="payment" id="payment"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required>
                            <option value="">Select Payment Method</option>
                            <option value="Cash">Cash</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Online Payment">Online Payment</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="total_price" class="block text-sm font-medium text-gray-700">Total Price</label>
                        <input type="number" step="0.01" name="total_price" id="total_price"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            readonly>
                    </div>
                    <div class="mb-4">
                        <label for="reference_number" class="block text-sm font-medium text-gray-700">Reference Number</label>
                        <input type="text" name="reference_number" id="reference_number"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            placeholder="Type to search or generate reference">
                        <div id="suggestions" class="bg-white border border-gray-300 rounded-lg mt-1 hidden"></div>
                        <button type="button" onclick="generateReferenceNumber()" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded-lg">Generate Reference</button>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg">Add Sale</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <?php include "include/footer.php"; ?>
</body>

</html>



<?php
session_start(); // Start session
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require "../dbconn.php";

// Get branch details
if (isset($_GET['id'])) {
    $branch_id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM branches WHERE branch_id = $branch_id");
    $branch = $result->fetch_assoc();
}

// Update branch
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $branch_id = intval($_POST['branch_id']);
    $location = $_POST['location'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];

    $sql = "UPDATE branches SET location='$location', contact_number='$contact_number', address='$address' WHERE branch_id=$branch_id";
    
    if ($conn->query($sql)) {
        $message = "Branch Updated Successfully.";
    } else {
        $message = "Failed to update branch.";
    }
    
    header("Location: manageBranch.php?id=$branch_id&message=" . urlencode($message));
    exit();
}

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
                <h1 class="text-2xl font-bold text-primary-700 mb-6">Edit Branch</h1>

                <!-- Success Message -->
                <?php if (isset($_GET['message'])): ?>
    <div id="success-message" class="bg-green-100 border border-green-500 text-green-700 px-4 py-3 rounded mb-4"> <?= htmlspecialchars($_GET['message']) ?>
    </div>
<?php endif; ?>
                
                <form method="POST" action="editBranch.php" class="bg-white p-6 rounded-lg shadow-md">
                    <input type="hidden" name="branch_id" value="<?= $branch['branch_id'] ?>">
                    <label class="block mb-2">Location</label>
                    <input type="text" name="location" value="<?= htmlspecialchars($branch['location']) ?>" class="w-full p-2 border rounded mb-4" required>
                    
                    <label class="block mb-2">Contact Number</label>
                    <input type="text" name="contact_number" value="<?= htmlspecialchars($branch['contact_number']) ?>" class="w-full p-2 border rounded mb-4" required>
                    
                    <label class="block mb-2">Address</label>
                    <textarea name="address" class="w-full p-2 border rounded mb-4" required><?= htmlspecialchars($branch['address']) ?></textarea>
                    
                    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Update Branch</button>
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
</html>
<?php
include '../dbconn.php';

function getCount($conn, $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    $row = $result->fetch_assoc();
    return $row['count'];
}

$counts = [
    "branches" => getCount($conn, "branches"),
    "brands" => getCount($conn, "brands"),
    "categories" => getCount($conn, "categories"),
    "medicines" => getCount($conn, "medicines"),
    "sales" => getCount($conn, "sales"),
    "suppliers" => getCount($conn, "suppliers"),
    
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Dashboard</title>
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
        <?php include('include/sidebar.php'); ?>
        
        <!-- Main Content -->
        <main class="flex-1 flex flex-col">
            <!-- Navbar -->
            <?php include "include/navbar.php"; ?>
            
            <div class="p-6 flex-1">
                <h1 class="text-2xl font-bold text-primary-700 mb-6">Dashboard Overview</h1>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($counts as $table => $count) { ?>
                        <div class="bg-white p-6 rounded-lg shadow text-center">
                            <h2 class="text-xl font-semibold text-primary-600"><?php echo ucfirst($table); ?></h2>
                            <p class="text-2xl font-bold"><?php echo $count; ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Footer -->
    <?php include "include/footer.php"; ?>
</body>
</html>
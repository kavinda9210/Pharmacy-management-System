<?php
// Ensure parent files have session_start()
$photo = $_SESSION['user_photo'] ?? '';
?>

<nav class="bg-white shadow-md p-4 flex justify-between items-center">
    <h1 class="text-xl font-bold text-primary-700">Pharmacy Dashboard</h1>
    <div class="flex items-center gap-4">
        <a href="profile.php" class="flex items-center gap-2 hover:opacity-80 transition-opacity group relative">
            <?php if (!empty($photo)): ?>
                <img src="<?= htmlspecialchars('../uploads/' . $user['photo']) ?>" 
                     alt="Profile Photo" 
                     class="w-10 h-10 rounded-full border-2 border-primary-500 object-cover shadow-sm">
                <span class="absolute -bottom-8 -right-2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                    View Profile
                </span>
            <?php else: ?>
                <div class="w-10 h-10 rounded-full bg-gray-200 border-2 border-primary-500 flex items-center justify-center">
                    <span class="text-gray-500 text-xs">No Photo</span>
                </div>
            <?php endif; ?>
        </a>
        <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
            Logout
        </a>
    </div>
</nav>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Hospital Management System' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <?php if (isset($showNavigation) && $showNavigation): ?>
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="/" class="text-2xl font-bold">HMS</a>
            
            <?php if ($auth->isAuthenticated()): ?>
            <div class="flex items-center space-x-4">
                <span>Welcome, <?= htmlspecialchars($auth->user()['first_name']) ?></span>
                <a href="/logout" class="hover:underline">Logout</a>
            </div>
            <?php else: ?>
            <div>
                <a href="/login" class="hover:underline mr-4">Login</a>
                <a href="/register" class="hover:underline">Register</a>
            </div>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <?= $content ?>
    </main>
    
    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; <?= date('Y') ?> Hospital Management System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
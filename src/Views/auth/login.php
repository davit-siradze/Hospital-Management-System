<?php
$content = ob_start();
?>

<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
    <h1 class="text-2xl font-bold text-center mb-6">Login to Your Account</h1>
    
    <?php if (!empty($errors['general'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= implode('<br>', $errors['general']) ?>
    </div>
    <?php endif; ?>
    
    <form action="/login" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $this->auth->generateCSRFToken() ?>">
        
        <div class="mb-4">
            <label for="email" class="block text-gray-700 mb-2">Email Address</label>
            <input type="email" id="email" name="email" 
                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 <?= !empty($errors['email']) ? 'border-red-500' : '' ?>"
                   value="<?= htmlspecialchars($old['email'] ?? '') ?>">
            <?php if (!empty($errors['email'])): ?>
            <p class="text-red-500 text-sm mt-1"><?= implode('<br>', $errors['email']) ?></p>
            <?php endif; ?>
        </div>
        
        <div class="mb-6">
            <label for="password" class="block text-gray-700 mb-2">Password</label>
            <input type="password" id="password" name="password" 
                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 <?= !empty($errors['password']) ? 'border-red-500' : '' ?>">
            <?php if (!empty($errors['password'])): ?>
            <p class="text-red-500 text-sm mt-1"><?= implode('<br>', $errors['password']) ?></p>
            <?php endif; ?>
        </div>
        
        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition duration-200">
            Login
        </button>
    </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
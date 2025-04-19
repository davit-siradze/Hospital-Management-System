<?php
$content = ob_start();
?>

<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
    <h1 class="text-2xl font-bold text-center mb-6">Create Your Account</h1>
    
    <?php if (isset($errors['general'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= implode('<br>', $errors['general']) ?>
    </div>
    <?php endif; ?>
    
    <form action="/register" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $auth->generateCSRFToken() ?>">
        
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label for="first_name" class="block text-gray-700 mb-2">First Name</label>
                <input type="text" id="first_name" name="first_name" 
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 <?= isset($errors['first_name']) ? 'border-red-500' : '' ?>"
                       value="<?= htmlspecialchars($old['first_name'] ?? '') ?>">
                <?php if (isset($errors['first_name'])): ?>
                <p class="text-red-500 text-sm mt-1"><?= implode('<br>', $errors['first_name']) ?></p>
                <?php endif; ?>
            </div>
            
            <div>
                <label for="last_name" class="block text-gray-700 mb-2">Last Name</label>
                <input type="text" id="last_name" name="last_name" 
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 <?= isset($errors['last_name']) ? 'border-red-500' : '' ?>"
                       value="<?= htmlspecialchars($old['last_name'] ?? '') ?>">
                <?php if (isset($errors['last_name'])): ?>
                <p class="text-red-500 text-sm mt-1"><?= implode('<br>', $errors['last_name']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mb-4">
            <label for="email" class="block text-gray-700 mb-2">Email Address</label>
            <input type="email" id="email" name="email" 
                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 <?= isset($errors['email']) ? 'border-red-500' : '' ?>"
                   value="<?= htmlspecialchars($old['email'] ?? '') ?>">
            <?php if (isset($errors['email'])): ?>
            <p class="text-red-500 text-sm mt-1"><?= implode('<br>', $errors['email']) ?></p>
            <?php endif; ?>
        </div>
        
        <div class="mb-4">
            <label for="phone" class="block text-gray-700 mb-2">Phone Number</label>
            <input type="tel" id="phone" name="phone" 
                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 <?= isset($errors['phone']) ? 'border-red-500' : '' ?>"
                   value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
            <?php if (isset($errors['phone'])): ?>
            <p class="text-red-500 text-sm mt-1"><?= implode('<br>', $errors['phone']) ?></p>
            <?php endif; ?>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Register As</label>
            <div class="flex space-x-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="role" value="patient" class="form-radio h-4 w-4 text-blue-600" <?= (isset($old['role']) && $old['role'] === 'patient') ? 'checked' : 'checked' ?>>
                    <span class="ml-2">Patient</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="role" value="doctor" class="form-radio h-4 w-4 text-blue-600" <?= (isset($old['role']) && $old['role'] === 'doctor') ? 'checked' : '' ?>>
                    <span class="ml-2">Doctor</span>
                </label>
            </div>
            <?php if (isset($errors['role'])): ?>
            <p class="text-red-500 text-sm mt-1"><?= implode('<br>', $errors['role']) ?></p>
            <?php endif; ?>
        </div>
        
        <div class="mb-4">
            <label for="password" class="block text-gray-700 mb-2">Password</label>
            <input type="password" id="password" name="password" 
                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 <?= isset($errors['password']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['password'])): ?>
            <p class="text-red-500 text-sm mt-1"><?= implode('<br>', $errors['password']) ?></p>
            <?php endif; ?>
        </div>
        
        <div class="mb-6">
            <label for="password_confirmation" class="block text-gray-700 mb-2">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" 
                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 <?= isset($errors['password_confirmation']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['password_confirmation'])): ?>
            <p class="text-red-500 text-sm mt-1"><?= implode('<br>', $errors['password_confirmation']) ?></p>
            <?php endif; ?>
        </div>
        
        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition duration-200">
            Register
        </button>
        
        <div class="mt-4 text-center">
            <p class="text-gray-600">Already have an account? <a href="/login" class="text-blue-600 hover:underline">Login here</a></p>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
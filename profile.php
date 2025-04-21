<?php 
include 'includes/config.php';

if (!isLoggedIn()) {
    redirect('login-registration.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    
    try {
        // Check if email is already taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Email already in use by another account";
        } else {
            // Update user info
            $update_stmt = $pdo->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, email = ? 
                WHERE id = ?
            ");
            $update_stmt->execute([$first_name, $last_name, $email, $_SESSION['user_id']]);
            
            // Update session
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['email'] = $email;
            
            $success = "Profile updated successfully";
        }
    } catch(PDOException $e) {
        $error = "Error updating profile: " . $e->getMessage();
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $password_error = "New passwords do not match";
    } elseif (strlen($new_password) < 8) {
        $password_error = "Password must be at least 8 characters";
    } else {
        try {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($current_password, $user['password'])) {
                $password_error = "Current password is incorrect";
            } else {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->execute([$hashed_password, $_SESSION['user_id']]);
                
                $password_success = "Password changed successfully";
            }
        } catch(PDOException $e) {
            $password_error = "Error changing password: " . $e->getMessage();
        }
    }
}

// Get current user info
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        die("User not found");
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

include 'includes/header.php'; 
?>

<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-8">My Profile</h1>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Personal Information</h2>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-gray-700 mb-2">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label for="last_name" class="block text-gray-700 mb-2">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>
                
                <div>
                    <label for="email" class="block text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2">Role</label>
                    <div class="px-4 py-2 border rounded-lg bg-gray-100">
                        <?php echo ucfirst($user['role']); ?>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">Update Profile</button>
            </form>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Change Password</h2>
            
            <?php if (isset($password_error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $password_error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($password_success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $password_success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label for="current_password" class="block text-gray-700 mb-2">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <label for="new_password" class="block text-gray-700 mb-2">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required minlength="8">
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-gray-700 mb-2">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required minlength="8">
                </div>
                
                <button type="submit" name="change_password" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">Change Password</button>
            </form>
        </div>
    </div>
    
    <?php if (isDoctor()): ?>
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Doctor Profile</h2>
            <a href="doctor/profile.php" class="inline-block bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 transition">Edit Doctor Profile</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
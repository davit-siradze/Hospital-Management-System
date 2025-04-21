<?php 
include 'includes/config.php';

$token = isset($_GET['token']) ? sanitizeInput($_GET['token']) : null;

// Verify token
if ($token) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $reset_request = $stmt->fetch();
        
        if (!$reset_request) {
            $token_error = "Invalid or expired token";
        }
    } catch(PDOException $e) {
        $token_error = "Error: " . $e->getMessage();
    }
} else {
    $token_error = "No token provided";
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password']) && !isset($token_error)) {
    $password = sanitizeInput($_POST['password']);
    $confirm_password = sanitizeInput($_POST['confirm_password']);
    
    if ($password !== $confirm_password) {
        $reset_error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $reset_error = "Password must be at least 8 characters";
    } else {
        try {
            // Update password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update_stmt->execute([$hashed_password, $reset_request['email']]);
            
            // Delete token
            $delete_stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $delete_stmt->execute([$token]);
            
            $reset_success = "Password has been reset successfully. You can now <a href='login-registration.php' class='text-blue-600'>login</a>.";
        } catch(PDOException $e) {
            $reset_error = "Error: " . $e->getMessage();
        }
    }
}

include 'includes/header.php'; 
?>

<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8 my-10">
    <h2 class="text-2xl font-bold mb-6">Reset Your Password</h2>
    
    <?php if (isset($token_error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $token_error; ?>
        </div>
    <?php elseif (isset($reset_error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $reset_error; ?>
        </div>
    <?php elseif (isset($reset_success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $reset_success; ?>
        </div>
    <?php else: ?>
        <form method="POST" class="space-y-4">
            <div>
                <label for="password" class="block text-gray-700 mb-2">New Password</label>
                <input type="password" id="password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required minlength="8">
            </div>
            <div>
                <label for="confirm_password" class="block text-gray-700 mb-2">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required minlength="8">
            </div>
            <button type="submit" name="reset_password" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition font-semibold">Reset Password</button>
        </form>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
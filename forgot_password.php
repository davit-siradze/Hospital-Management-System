<?php 
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    
    try {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            // Generate token
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
            
            // Delete any existing tokens for this email
            $delete_stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $delete_stmt->execute([$email]);
            
            // Insert new token
            $insert_stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $insert_stmt->execute([$email, $token, $expires]);
            
            // Send email (in a real app, you would send an actual email)
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=$token";
            $message = "Click the following link to reset your password: $reset_link";
            
            // For demo purposes, we'll just show the link
            $reset_success = "Password reset link has been generated. For demo purposes: <a href='$reset_link' class='text-blue-600'>$reset_link</a>";
        } else {
            $reset_error = "Email not found in our system";
        }
    } catch(PDOException $e) {
        $reset_error = "Error: " . $e->getMessage();
    }
}

include 'includes/header.php'; 
?>

<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8 my-10">
    <h2 class="text-2xl font-bold mb-6">Reset Your Password</h2>
    
    <?php if (isset($reset_error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $reset_error; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($reset_success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $reset_success; ?>
        </div>
    <?php endif; ?>
    
    <p class="text-gray-600 mb-6">Enter your email address and we'll send you a link to reset your password.</p>
    
    <form method="POST" class="space-y-4">
        <div>
            <label for="email" class="block text-gray-700 mb-2">Email Address</label>
            <input type="email" id="email" name="email" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition font-semibold">Send Reset Link</button>
    </form>
    
    <div class="mt-6 text-center">
        <a href="login-registration.php" class="text-blue-600 hover:text-blue-800">Back to Login</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
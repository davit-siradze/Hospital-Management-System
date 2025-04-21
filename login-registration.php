<?php 
include 'includes/config.php';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = sanitizeInput($_POST['email']);
    $password = sanitizeInput($_POST['password']);
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                redirect('admin/dashboard.php');
            } elseif ($user['role'] === 'doctor') {
                redirect('doctor/dashboard.php');
            } else {
                redirect('index.php');
            }
        } else {
            $login_error = "Invalid email or password";
        }
    } catch(PDOException $e) {
        $login_error = "Error: " . $e->getMessage();
    }
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $password = sanitizeInput($_POST['password']);
    $confirm_password = sanitizeInput($_POST['confirm_password']);
    
    // Validate inputs
    if ($password !== $confirm_password) {
        $register_error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $register_error = "Password must be at least 8 characters";
    } else {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $register_error = "Email already exists";
            } else {
                // Create username
                $username = strtolower($first_name) . '.' . strtolower($last_name);
                $counter = 1;
                
                // Check if username exists and increment if needed
                $username_stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $username_stmt->execute([$username]);
                
                while ($username_stmt->rowCount() > 0) {
                    $username = strtolower($first_name) . '.' . strtolower($last_name) . $counter;
                    $username_stmt->execute([$username]);
                    $counter++;
                }
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user
                $insert_stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, 'patient')");
                $insert_stmt->execute([$username, $email, $hashed_password, $first_name, $last_name]);
                
                // Auto-login after registration
                $user_id = $pdo->lastInsertId();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'patient';
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                
                redirect('index.php');
            }
        } catch(PDOException $e) {
            $register_error = "Error: " . $e->getMessage();
        }
    }
}

include 'includes/header.php'; 
?>

<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8 my-10">
    <ul class="flex border-b mb-8">
        <li class="mr-1">
            <a href="#login" class="tab-btn bg-blue-500 text-white inline-block py-2 px-4 rounded-t-lg font-semibold">Login</a>
        </li>
        <li class="mr-1">
            <a href="#register" class="tab-btn bg-white inline-block py-2 px-4 rounded-t-lg font-semibold hover:bg-gray-100">Register</a>
        </li>
    </ul>

    <div id="login" class="tab-content">
        <h2 class="text-2xl font-bold mb-6">Login to Your Account</h2>
        <?php if (isset($login_error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $login_error; ?>
            </div>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <div>
                <label for="login-email" class="block text-gray-700 mb-2">Email</label>
                <input type="email" id="login-email" name="email" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="login-password" class="block text-gray-700 mb-2">Password</label>
                <input type="password" id="login-password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input type="checkbox" id="remember-me" name="remember" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 block text-gray-700">Remember me</label>
                </div>
                <a href="forgot_password.php" class="text-blue-600 hover:text-blue-800">Forgot password?</a>
            </div>
            <button type="submit" name="login" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition font-semibold">Login</button>
        </form>
    </div>

    <div id="register" class="tab-content hidden">
        <h2 class="text-2xl font-bold mb-6">Create an Account</h2>
        <?php if (isset($register_error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $register_error; ?>
            </div>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="first-name" class="block text-gray-700 mb-2">First Name</label>
                    <input type="text" id="first-name" name="first_name" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label for="last-name" class="block text-gray-700 mb-2">Last Name</label>
                    <input type="text" id="last-name" name="last_name" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>
            <div>
                <label for="register-email" class="block text-gray-700 mb-2">Email</label>
                <input type="email" id="register-email" name="email" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="register-password" class="block text-gray-700 mb-2">Password</label>
                <input type="password" id="register-password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required minlength="8">
            </div>
            <div>
                <label for="confirm-password" class="block text-gray-700 mb-2">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm_password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required minlength="8">
            </div>
            <div class="flex items-center">
                <input type="checkbox" id="terms" name="terms" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" required>
                <label for="terms" class="ml-2 block text-gray-700">I agree to the <a href="#" class="text-blue-600 hover:text-blue-800">Terms and Conditions</a></label>
            </div>
            <button type="submit" name="register" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition font-semibold">Register</button>
        </form>
    </div>
</div>

<script>
    // Tab switching functionality
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Update active tab
            document.querySelectorAll('.tab-btn').forEach(tab => {
                tab.classList.remove('bg-blue-500', 'text-white');
                tab.classList.add('bg-white', 'hover:bg-gray-100');
            });
            this.classList.remove('bg-white', 'hover:bg-gray-100');
            this.classList.add('bg-blue-500', 'text-white');
            
            // Show corresponding content
            const target = this.getAttribute('href').substring(1);
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            document.getElementById(target).classList.remove('hidden');
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
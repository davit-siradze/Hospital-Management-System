<?php
include '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login-registration.php');
}

// Get all specializations for the dropdown
$specializations = $pdo->query("SELECT * FROM specializations ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitizeInput($_POST['role']);
    $specialization_id = ($role === 'doctor') ? (int)$_POST['specialization_id'] : null;

    // Validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } else {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Email already exists";
            } else {
                // Create username
                $username = strtolower($first_name) . '.' . strtolower($last_name);
                $counter = 1;
                
                // Ensure unique username
                $username_stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $username_stmt->execute([$username]);
                
                while ($username_stmt->rowCount() > 0) {
                    $username = strtolower($first_name) . '.' . strtolower($last_name) . $counter;
                    $username_stmt->execute([$username]);
                    $counter++;
                }
                
                // Hash password and create user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $pdo->beginTransaction();
                
                // Insert user
              // Insert user
$insert_stmt = $pdo->prepare("
INSERT INTO users (username, email, password, first_name, last_name, role) 
VALUES (?, ?, ?, ?, ?, ?)
");
$insert_stmt->execute([$username, $email, $hashed_password, $first_name, $last_name, $role]);

// Get user_id
$user_id = $pdo->lastInsertId();
echo "User ID: $user_id"; // Debugging output

if ($role === 'doctor') {
if (!$specialization_id) {
    throw new Exception("Specialization is required for doctors");
}

// Check if user_id exists
$stmt_check = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$stmt_check->execute([$user_id]);
if ($stmt_check->rowCount() == 0) {
    throw new Exception("User ID does not exist in the users table");
}

// Insert into doctors table
$doctor_stmt = $pdo->prepare("
    INSERT INTO doctors (user_id, specialization_id, specialization, bio)
    VALUES (?, ?, ?, '')
");
$doctor_stmt->execute([$user_id, $specialization_id, $specialization_name]);
}

                
                $pdo->commit();
                $success = "User registered successfully!";
            }
        } catch(Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<!-- Registration form with specialization field for doctors -->
<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-8">Register New User</h1>
    
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $success; ?>
            <a href="users.php" class="inline-block mt-2 text-blue-600 hover:text-blue-800">View all users</a>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto">
        <form method="POST" class="space-y-4">
            <!-- Personal info fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-gray-700 mb-2">First Name</label>
                    <input type="text" id="first_name" name="first_name" required class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label for="last_name" class="block text-gray-700 mb-2">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required class="w-full px-4 py-2 border rounded-lg">
                </div>
            </div>
            
            <div>
                <label for="email" class="block text-gray-700 mb-2">Email</label>
                <input type="email" id="email" name="email" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" required minlength="8" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label for="confirm_password" class="block text-gray-700 mb-2">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8" class="w-full px-4 py-2 border rounded-lg">
                </div>
            </div>
            
            <div>
                <label for="role" class="block text-gray-700 mb-2">Role</label>
                <select id="role" name="role" required class="w-full px-4 py-2 border rounded-lg">
                    <option value="patient">Patient</option>
                    <option value="doctor">Doctor</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <!-- Doctor-specific fields (shown only when role=doctor) -->
            <div id="doctor-fields" class="hidden">
                <div>
                    <label for="specialization_id" class="block text-gray-700 mb-2">Specialization</label>
                    <select id="specialization_id" name="specialization_id" class="w-full px-4 py-2 border rounded-lg">
                        <option value="">Select Specialization</option>
                        <?php foreach ($specializations as $spec): ?>
                            <option value="<?= $spec['id'] ?>"><?= htmlspecialchars($spec['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700">
                Register User
            </button>
        </form>
    </div>
</div>

<script>
// Show/hide doctor fields based on role selection
document.getElementById('role').addEventListener('change', function() {
    const doctorFields = document.getElementById('doctor-fields');
    doctorFields.classList.toggle('hidden', this.value !== 'doctor');
    
    // Make specialization required only for doctors
    document.getElementById('specialization_id').required = (this.value === 'doctor');
});
</script>

<?php include '../includes/footer.php'; ?>

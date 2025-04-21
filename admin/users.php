<?php 
include '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login-registration.php');
}

// Handle role updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = (int)$_POST['user_id'];
    $role = sanitizeInput($_POST['role']);
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $user_id]);
        
        // If setting as doctor, ensure there's a doctor record
        if ($role === 'doctor') {
            $check_stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
            $check_stmt->execute([$user_id]);
            
            if ($check_stmt->rowCount() === 0) {
                $insert_stmt = $pdo->prepare("INSERT INTO doctors (user_id, specialization) VALUES (?, 'General')");
                $insert_stmt->execute([$user_id]);
            }
        }
        
        $success = "User role updated successfully";
    } catch(PDOException $e) {
        $error = "Error updating user: " . $e->getMessage();
    }
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    try {
        // Check if user is a doctor with appointments
        $stmt = $pdo->prepare("
            SELECT a.id 
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            WHERE d.user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$user_id]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Cannot delete doctor with existing appointments";
        } else {
            // Delete user (cascade will handle doctors table)
            $delete_stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $delete_stmt->execute([$user_id]);
            $success = "User deleted successfully";
        }
    } catch(PDOException $e) {
        $error = "Error deleting user: " . $e->getMessage();
    }
}

// Get all users
try {
    $query = "
        SELECT u.*, 
               CASE 
                   WHEN d.id IS NOT NULL THEN d.specialization
                   ELSE ''
               END AS specialization
        FROM users u
        LEFT JOIN doctors d ON u.id = d.user_id
        ORDER BY u.role, u.last_name
    ";
    $users = $pdo->query($query)->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

include '../includes/header.php'; 
?>

<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-8">Manage Users</h1>
    
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
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="overflow-x-auto">
                <a href="register_user.php" class="block bg-indigo-100 text-indigo-800 p-4 rounded-lg hover:bg-indigo-200 transition flex items-center">
            <i class="fas fa-user-plus text-2xl mr-3"></i>
            <span>Register New User</span>
        </a>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialization</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $user['email']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <select name="role" onchange="this.form.submit()" class="border rounded px-2 py-1 text-sm <?php echo $user['role'] === 'admin' ? 'bg-gray-100' : ''; ?>" <?php echo $user['role'] === 'admin' ? 'disabled' : ''; ?>>
                                        <option value="patient" <?php echo $user['role'] === 'patient' ? 'selected' : ''; ?>>Patient</option>
                                        <option value="doctor" <?php echo $user['role'] === 'doctor' ? 'selected' : ''; ?>>Doctor</option>
                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    <input type="hidden" name="update_role">
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $user['specialization']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" onclick="return confirm('Are you sure you want to delete this user?')" class="text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
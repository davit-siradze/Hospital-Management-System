<?php 
include '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login-registration.php');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_doctor'])) {
        // Add new doctor
        $user_id = (int)$_POST['user_id'];
        $specialization = sanitizeInput($_POST['specialization']);
        $bio = sanitizeInput($_POST['bio']);
        
        try {
            // Check if user exists and is not already a doctor
            $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $error = "User not found";
            } elseif ($user['role'] !== 'doctor') {
                $error = "Selected user is not registered as a doctor";
            } else {
                // Check if doctor already exists
                $check_stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
                $check_stmt->execute([$user_id]);
                
                if ($check_stmt->rowCount() > 0) {
                    $error = "This user is already registered as a doctor";
                } else {
                    // Insert new doctor
                    $insert_stmt = $pdo->prepare("INSERT INTO doctors (user_id, specialization, bio) VALUES (?, ?, ?)");
                    $insert_stmt->execute([$user_id, $specialization, $bio]);
                    $success = "Doctor added successfully";
                }
            }
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_doctor'])) {
        // Update doctor
        $doctor_id = (int)$_POST['doctor_id'];
        $specialization = sanitizeInput($_POST['specialization']);
        $bio = sanitizeInput($_POST['bio']);
        
        try {
            $stmt = $pdo->prepare("UPDATE doctors SET specialization = ?, bio = ? WHERE id = ?");
            $stmt->execute([$specialization, $bio, $doctor_id]);
            $success = "Doctor updated successfully";
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get all doctors
try {
    $stmt = $pdo->query("
        SELECT d.*, u.first_name, u.last_name, u.email, u.role
        FROM doctors d
        JOIN users u ON d.user_id = u.id
        ORDER BY d.specialization, u.last_name
    ");
    $doctors = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Get all users who could be doctors (for add form)
try {
    $stmt = $pdo->query("
        SELECT id, first_name, last_name, email 
        FROM users 
        WHERE role = 'doctor' 
        AND id NOT IN (SELECT user_id FROM doctors)
        ORDER BY last_name
    ");
    $potential_doctors = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

include '../includes/header.php'; 
?>

<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-8">Manage Doctors</h1>
    
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
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Current Doctors</h2>
                
                <?php if (count($doctors) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialization</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($doctors as $doctor): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">Dr. <?php echo $doctor['first_name'] . ' ' . $doctor['last_name']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $doctor['specialization']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $doctor['email']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button onclick="openEditModal(<?php echo $doctor['id']; ?>, '<?php echo htmlspecialchars($doctor['specialization']); ?>', `<?php echo htmlspecialchars($doctor['bio']); ?>`)" class="text-blue-600 hover:text-blue-800 mr-2">Edit</button>
                                            <a href="doctor_schedule.php?id=<?php echo $doctor['id']; ?>" class="text-green-600 hover:text-green-800 mr-2">Schedule</a>
                                            <a href="delete_doctor.php?id=<?php echo $doctor['id']; ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Are you sure you want to delete this doctor?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600">No doctors found.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Add New Doctor</h2>
                <form method="POST" class="space-y-4">
                    <div>
                        <label for="user_id" class="block text-gray-700 mb-2">Select User</label>
                        <select id="user_id" name="user_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select a user</option>
                            <?php foreach ($potential_doctors as $user): ?>
                                <option value="<?php echo $user['id']; ?>"><?php echo $user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ')'; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (count($potential_doctors) === 0): ?>
                            <p class="text-sm text-gray-500 mt-1">No users available to be assigned as doctors. <a href="users.php" class="text-blue-600">Register new doctors first</a>.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="specialization" class="block text-gray-700 mb-2">Specialization</label>
                        <input type="text" id="specialization" name="specialization" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div>
                        <label for="bio" class="block text-gray-700 mb-2">Bio (Optional)</label>
                        <textarea id="bio" name="bio" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <button type="submit" name="add_doctor" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">Add Doctor</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Doctor Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-xl font-semibold mb-4">Edit Doctor</h3>
        <form method="POST" id="editForm" class="space-y-4">
            <input type="hidden" id="edit_doctor_id" name="doctor_id">
            
            <div>
                <label for="edit_specialization" class="block text-gray-700 mb-2">Specialization</label>
                <input type="text" id="edit_specialization" name="specialization" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <div>
                <label for="edit_bio" class="block text-gray-700 mb-2">Bio</label>
                <textarea id="edit_bio" name="bio" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeEditModal()" class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600 transition">Cancel</button>
                <button type="submit" name="update_doctor" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, specialization, bio) {
        document.getElementById('edit_doctor_id').value = id;
        document.getElementById('edit_specialization').value = specialization;
        document.getElementById('edit_bio').value = bio;
        document.getElementById('editModal').classList.remove('hidden');
    }
    
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
    
    // Close modal when clicking outside
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
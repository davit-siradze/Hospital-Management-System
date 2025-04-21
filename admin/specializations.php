<?php 
include '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login-registration.php');
}

// Create specializations table if it doesn't exist
$create_table = "
CREATE TABLE IF NOT EXISTS specializations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$pdo->exec($create_table);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_specialization'])) {
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO specializations (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            $success = "Specialization added successfully";
        } catch(PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $error = "Specialization already exists";
            } else {
                $error = "Error: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_specialization'])) {
        $id = (int)$_POST['id'];
        
        try {
            // Check if specialization is in use
            $stmt = $pdo->prepare("
                SELECT id FROM doctors 
                WHERE specialization_id = ?
                LIMIT 1
            ");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Cannot delete specialization in use by doctors";
            } else {
                $delete_stmt = $pdo->prepare("DELETE FROM specializations WHERE id = ?");
                $delete_stmt->execute([$id]);
                $success = "Specialization deleted successfully";
            }
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get all specializations
try {
    $specializations = $pdo->query("SELECT * FROM specializations ORDER BY name")->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

include '../includes/header.php'; 
?>

<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-8">Manage Specializations</h1>
    
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
                <h2 class="text-xl font-semibold mb-4">Current Specializations</h2>
                
                <?php if (count($specializations) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($specializations as $spec): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $spec['name']; ?></td>
                                        <td class="px-6 py-4"><?php echo $spec['description'] ?: 'N/A'; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="id" value="<?php echo $spec['id']; ?>">
                                                <button type="submit" name="delete_specialization" onclick="return confirm('Are you sure you want to delete this specialization?')" class="text-red-600 hover:text-red-800">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600">No specializations found.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Add New Specialization</h2>
                <form method="POST" class="space-y-4">
                    <div>
                        <label for="name" class="block text-gray-700 mb-2">Specialization Name</label>
                        <input type="text" id="name" name="name" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-gray-700 mb-2">Description (Optional)</label>
                        <textarea id="description" name="description" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <button type="submit" name="add_specialization" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">Add Specialization</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
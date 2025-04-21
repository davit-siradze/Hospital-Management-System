<?php 
include '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login-registration.php');
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $status = sanitizeInput($_POST['status']);
    
    try {
        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $stmt->execute([$status, $appointment_id]);
        $success = "Appointment status updated successfully";
    } catch(PDOException $e) {
        $error = "Error updating appointment: " . $e->getMessage();
    }
}

// Get filter parameters
$filter_status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$filter_doctor = isset($_GET['doctor']) ? (int)$_GET['doctor'] : '';
$filter_date = isset($_GET['date']) ? sanitizeInput($_GET['date']) : '';

// Build query with filters
$query = "
    SELECT a.*, 
           CONCAT(up.first_name, ' ', up.last_name) AS patient_name,
           CONCAT(ud.first_name, ' ', ud.last_name) AS doctor_name,
           d.specialization
    FROM appointments a
    JOIN users up ON a.patient_id = up.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users ud ON d.user_id = ud.id
";

$params = [];
$where = [];

if ($filter_status) {
    $where[] = "a.status = ?";
    $params[] = $filter_status;
}

if ($filter_doctor) {
    $where[] = "a.doctor_id = ?";
    $params[] = $filter_doctor;
}

if ($filter_date) {
    $where[] = "DATE(a.appointment_date) = ?";
    $params[] = $filter_date;
}

if (count($where) > 0) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY a.appointment_date DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $appointments = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Get all doctors for filter
try {
    $doctors = $pdo->query("
        SELECT d.id, CONCAT(u.first_name, ' ', u.last_name) AS name
        FROM doctors d
        JOIN users u ON d.user_id = u.id
        ORDER BY u.last_name
    ")->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

include '../includes/header.php'; 
?>

<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-8">Manage Appointments</h1>
    
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
    
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Filters</h2>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="status" class="block text-gray-700 mb-2">Status</label>
                <select id="status" name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $filter_status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="completed" <?php echo $filter_status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            
            <div>
                <label for="doctor" class="block text-gray-700 mb-2">Doctor</label>
                <select id="doctor" name="doctor" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Doctors</option>
                    <?php foreach ($doctors as $doctor): ?>
                        <option value="<?php echo $doctor['id']; ?>" <?php echo $filter_doctor === $doctor['id'] ? 'selected' : ''; ?>>
                            Dr. <?php echo $doctor['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="date" class="block text-gray-700 mb-2">Date</label>
                <input type="date" id="date" name="date" value="<?php echo $filter_date; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition h-[42px]">Apply Filters</button>
                <?php if ($filter_status || $filter_doctor || $filter_date): ?>
                    <a href="appointments.php" class="ml-2 bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600 transition h-[42px] flex items-center">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Appointments</h2>
        
        <?php if (count($appointments) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialization</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($appointments as $appointment): ?>
                            <?php
                            $status_class = '';
                            switch ($appointment['status']) {
                                case 'confirmed':
                                    $status_class = 'bg-green-100 text-green-800';
                                    break;
                                case 'pending':
                                    $status_class = 'bg-yellow-100 text-yellow-800';
                                    break;
                                case 'cancelled':
                                    $status_class = 'bg-red-100 text-red-800';
                                    break;
                                case 'completed':
                                    $status_class = 'bg-blue-100 text-blue-800';
                                    break;
                            }
                            ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $appointment['patient_name']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">Dr. <?php echo $appointment['doctor_name']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $appointment['specialization']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M j, Y g:i A', strtotime($appointment['appointment_date'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button onclick="openStatusModal(<?php echo $appointment['id']; ?>, '<?php echo $appointment['status']; ?>')" class="text-blue-600 hover:text-blue-800">Update Status</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-600">No appointments found matching your criteria.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-xl font-semibold mb-4">Update Appointment Status</h3>
        <form method="POST" id="statusForm" class="space-y-4">
            <input type="hidden" id="appointment_id" name="appointment_id">
            
            <div>
                <label for="status" class="block text-gray-700 mb-2">New Status</label>
                <select id="status" name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeStatusModal()" class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600 transition">Cancel</button>
                <button type="submit" name="update_status" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openStatusModal(id, currentStatus) {
        document.getElementById('appointment_id').value = id;
        document.getElementById('status').value = currentStatus;
        document.getElementById('statusModal').classList.remove('hidden');
    }
    
    function closeStatusModal() {
        document.getElementById('statusModal').classList.add('hidden');
    }
    
    // Close modal when clicking outside
    document.getElementById('statusModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeStatusModal();
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../includes/config.php';


if (!isLoggedIn() || !isAdmin()) {
    redirect('../login-registration.php');
}

// Get stats
try {
    $users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $doctors_count = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
    $appointments_count = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
    $pending_appointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetchColumn();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

include '../includes/header.php'; 
?>

<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-8">Admin Dashboard</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-500 text-sm font-semibold mb-1">Total Users</h3>
            <p class="text-3xl font-bold text-blue-600"><?php echo $users_count; ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-500 text-sm font-semibold mb-1">Total Doctors</h3>
            <p class="text-3xl font-bold text-green-600"><?php echo $doctors_count; ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-500 text-sm font-semibold mb-1">Total Appointments</h3>
            <p class="text-3xl font-bold text-purple-600"><?php echo $appointments_count; ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-500 text-sm font-semibold mb-1">Pending Appointments</h3>
            <p class="text-3xl font-bold text-yellow-600"><?php echo $pending_appointments; ?></p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Recent Appointments</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        try {
                            $stmt = $pdo->query("
                                SELECT a.*, 
                                       CONCAT(up.first_name, ' ', up.last_name) AS patient_name,
                                       CONCAT(ud.first_name, ' ', ud.last_name) AS doctor_name
                                FROM appointments a
                                JOIN users up ON a.patient_id = up.id
                                JOIN doctors d ON a.doctor_id = d.id
                                JOIN users ud ON d.user_id = ud.id
                                ORDER BY a.appointment_date DESC
                                LIMIT 5
                            ");
                            
                            while ($row = $stmt->fetch()) {
                                $status_class = '';
                                switch ($row['status']) {
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
                                
                                echo "
                                <tr>
                                    <td class=\"px-6 py-4 whitespace-nowrap\">{$row['patient_name']}</td>
                                    <td class=\"px-6 py-4 whitespace-nowrap\">{$row['doctor_name']}</td>
                                    <td class=\"px-6 py-4 whitespace-nowrap\">" . date('M j, Y g:i A', strtotime($row['appointment_date'])) . "</td>
                                    <td class=\"px-6 py-4 whitespace-nowrap\">
                                        <span class=\"px-2 inline-flex text-xs leading-5 font-semibold rounded-full {$status_class}\">
                                            " . ucfirst($row['status']) . "
                                        </span>
                                    </td>
                                </tr>
                                ";
                                
                            }
                        } catch(PDOException $e) {
                            echo "<tr><td colspan='4' class='px-6 py-4 text-center'>Error loading appointments</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 text-right">
                <a href="appointments.php" class="text-blue-600 hover:text-blue-800">View All</a>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="doctors.php" class="bg-blue-100 text-blue-800 p-4 rounded-lg hover:bg-blue-200 transition flex items-center">
                    <i class="fas fa-user-md text-2xl mr-3"></i>
                    <span>Manage Doctors</span>
                </a>
                <a href="users.php" class="bg-green-100 text-green-800 p-4 rounded-lg hover:bg-green-200 transition flex items-center">
                    <i class="fas fa-users text-2xl mr-3"></i>
                    <span>Manage Users</span>
                </a>
                <a href="appointments.php" class="bg-purple-100 text-purple-800 p-4 rounded-lg hover:bg-purple-200 transition flex items-center">
                    <i class="fas fa-calendar-check text-2xl mr-3"></i>
                    <span>Manage Appointments</span>
                </a>
                <a href="specializations.php" class="bg-yellow-100 text-yellow-800 p-4 rounded-lg hover:bg-yellow-200 transition flex items-center">
                    <i class="fas fa-stethoscope text-2xl mr-3"></i>
                    <span>Manage Specializations</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
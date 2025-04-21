<?php 
include '../includes/config.php';

if (!isLoggedIn() || !isPatient()) {
    redirect('../login-registration.php');
}

// Get upcoming appointments
try {
    $stmt = $pdo->prepare("
        SELECT a.*, 
               CONCAT(u.first_name, ' ', u.last_name) AS doctor_name,
               d.specialization
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users u ON d.user_id = u.id
        WHERE a.patient_id = ? 
        AND a.appointment_date >= NOW()
        AND a.status != 'cancelled'
        ORDER BY a.appointment_date ASC
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $upcoming_appointments = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Get past appointments count
try {
    $past_appointments_count = $pdo->prepare("
        SELECT COUNT(*) 
        FROM appointments 
        WHERE patient_id = ? 
        AND (appointment_date < NOW() OR status = 'completed')
    ")->execute([$_SESSION['user_id']])->fetchColumn();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

include '../includes/header.php'; 
?>

<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-8">Patient Dashboard</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-500 text-sm font-semibold mb-1">Upcoming Appointments</h3>
            <p class="text-3xl font-bold text-blue-600"><?php echo count($upcoming_appointments); ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-500 text-sm font-semibold mb-1">Past Appointments</h3>
            <p class="text-3xl font-bold text-green-600"><?php echo $past_appointments_count; ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-500 text-sm font-semibold mb-1">Quick Actions</h3>
            <a href="../booking_appointment.php" class="inline-block bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition text-sm">Book New Appointment</a>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Upcoming Appointments</h2>
        
        <?php if (count($upcoming_appointments) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialization</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($upcoming_appointments as $appointment): ?>
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
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $appointment['doctor_name']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $appointment['specialization']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M j, Y g:i A', strtotime($appointment['appointment_date'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="appointment_details.php?id=<?php echo $appointment['id']; ?>" class="text-blue-600 hover:text-blue-800 mr-2">View</a>
                                    <?php if ($appointment['status'] === 'pending' || $appointment['status'] === 'confirmed'): ?>
                                        <a href="cancel_appointment.php?id=<?php echo $appointment['id']; ?>" class="text-red-600 hover:text-red-800">Cancel</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-600">You have no upcoming appointments. <a href="../booking_appointment.php" class="text-blue-600 hover:text-blue-800">Book one now</a>.</p>
        <?php endif; ?>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Medical History</h2>
            <p class="text-gray-600 mb-4">View your past medical appointments and records.</p>
            <a href="medical_history.php" class="inline-block bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">View History</a>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Profile Settings</h2>
            <p class="text-gray-600 mb-4">Update your personal information and preferences.</p>
            <a href="profile.php" class="inline-block bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 transition">Edit Profile</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
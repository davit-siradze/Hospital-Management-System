<?php
include '../includes/config.php';

if (!isLoggedIn()) {
    redirect('../login-registration.php');
}

// Ensure the user is actually a doctor
try {
    $stmt = $pdo->prepare("
        SELECT d.id AS doctor_id, d.specialization_id, s.name AS specialization
        FROM doctors d
        LEFT JOIN specializations s ON d.specialization_id = s.id
        WHERE d.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $doctor = $stmt->fetch();
    
    if (!$doctor) {
        // User has doctor role but no doctor record - fix this inconsistency
        if ($_SESSION['role'] === 'doctor') {
            $insert_stmt = $pdo->prepare("
                INSERT INTO doctors (user_id, specialization_id, bio)
                VALUES (?, NULL, '')
            ");
            $insert_stmt->execute([$_SESSION['user_id']]);
            redirect('dashboard.php'); // Refresh to load new doctor record
        } else {
            // User shouldn't be here
            redirect('../index.php');
        }
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

try {
    $stmt = $pdo->prepare("
        SELECT a.*, 
               CONCAT(u.first_name, ' ', u.last_name) AS patient_name
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        WHERE a.doctor_id = ?
        AND a.appointment_date >= NOW()
        ORDER BY a.appointment_date ASC
        LIMIT 10
    ");
    $stmt->execute([$doctor['doctor_id']]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error loading appointments: " . $e->getMessage());
}


include '../includes/header.php';
?>

<div class="container mx-auto py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Doctor Dashboard</h1>
        <div class="text-lg">
            Dr. <?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?>
            <?php if ($doctor['specialization']): ?>
                <span class="text-blue-600">(<?= htmlspecialchars($doctor['specialization']) ?>)</span>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-500 text-sm font-semibold mb-1">Today's Appointments</h3>
            <?php
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM appointments 
                    WHERE doctor_id = ? 
                    AND DATE(appointment_date) = CURDATE()
                ");
                $stmt->execute([$doctor['doctor_id']]);
                $today_count = $stmt->fetchColumn();
                ?>

            <p class="text-3xl font-bold text-blue-600"><?= $today_count ?></p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-500 text-sm font-semibold mb-1">Upcoming Appointments</h3>
            <?php
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM appointments 
                WHERE doctor_id = ? 
                AND appointment_date > NOW()
            ");
            $stmt->execute([$doctor['doctor_id']]);
            $upcoming_count = $stmt->fetchColumn();
            ?>

            <p class="text-3xl font-bold text-green-600"><?= $upcoming_count ?></p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-500 text-sm font-semibold mb-1">Quick Actions</h3>
            <div class="mt-2 space-x-2">
                <a href="schedule.php" class="inline-block bg-blue-600 text-white py-1 px-3 rounded text-sm">My Schedule</a>
                <a href="profile.php" class="inline-block bg-gray-600 text-white py-1 px-3 rounded text-sm">My Profile</a>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Upcoming Appointments</h2>
        
        <?php if (count($appointments) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($appointments as $appt): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($appt['patient_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= date('M j, Y g:i A', strtotime($appt['appointment_date'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= $appt['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                           ($appt['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                           'bg-gray-100 text-gray-800') ?>">
                                        <?= ucfirst($appt['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="appointment.php?id=<?= $appt['id'] ?>" class="text-blue-600 hover:text-blue-800">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-600">No upcoming appointments found.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<?php 
include '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login-registration.php');
}

$doctor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get doctor details
try {
    $stmt = $pdo->prepare("
        SELECT d.*, CONCAT(u.first_name, ' ', u.last_name) AS doctor_name
        FROM doctors d
        JOIN users u ON d.user_id = u.id
        WHERE d.id = ?
    ");
    $stmt->execute([$doctor_id]);
    $doctor = $stmt->fetch();
    
    if (!$doctor) {
        die("Doctor not found");
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Delete existing schedule for this doctor
        $delete_stmt = $pdo->prepare("DELETE FROM doctor_schedules WHERE doctor_id = ?");
        $delete_stmt->execute([$doctor_id]);
        
        // Insert new schedule
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $insert_stmt = $pdo->prepare("INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, is_available) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($days as $day) {
            $start_time = $_POST["{$day}_start"] ?? '09:00';
            $end_time = $_POST["{$day}_end"] ?? '17:00';
            $is_available = isset($_POST["{$day}_available"]) ? 1 : 0;
            
            $insert_stmt->execute([$doctor_id, $day, $start_time, $end_time, $is_available]);
        }
        
        $success_message = "Schedule updated successfully!";
    } catch(PDOException $e) {
        $error_message = "Error updating schedule: " . $e->getMessage();
    }
}

// Get current schedule
$schedule = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM doctor_schedules WHERE doctor_id = ?");
    $stmt->execute([$doctor_id]);
    
    while ($row = $stmt->fetch()) {
        $schedule[$row['day_of_week']] = [
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'is_available' => $row['is_available']
        ];
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Default schedule if none exists
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
foreach ($days as $day) {
    if (!isset($schedule[$day])) {
        $schedule[$day] = [
            'start_time' => '09:00',
            'end_time' => '17:00',
            'is_available' => ($day !== 'Saturday' && $day !== 'Sunday')
        ];
    }
}

include '../includes/header.php'; 
?>

<div class="container mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Manage Schedule for Dr. <?php echo $doctor['doctor_name']; ?></h1>
        <a href="doctors.php" class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600 transition">Back to Doctors</a>
    </div>
    
    <p class="text-gray-600 mb-6">Specialization: <?php echo $doctor['specialization']; ?></p>
    
    <?php if (isset($success_message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" class="bg-white rounded-lg shadow-md p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Time</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($days as $day): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-medium"><?php echo $day; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="<?php echo $day; ?>_available" <?php echo $schedule[$day]['is_available'] ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="time" name="<?php echo $day; ?>_start" value="<?php echo $schedule[$day]['start_time']; ?>" class="border rounded px-2 py-1">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="time" name="<?php echo $day; ?>_end" value="<?php echo $schedule[$day]['end_time']; ?>" class="border rounded px-2 py-1">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-6">
            <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded hover:bg-blue-700 transition font-semibold">Save Schedule</button>
        </div>
    </form>
    
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Current Availability</h2>
        <div id="calendar"></div>
    </div>
</div>

<!-- Include FullCalendar CSS and JS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: [
                <?php foreach ($days as $day): ?>
                    <?php if ($schedule[$day]['is_available']): ?>
                        {
                            title: 'Available',
                            startTime: '<?php echo $schedule[$day]['start_time']; ?>',
                            endTime: '<?php echo $schedule[$day]['end_time']; ?>',
                            daysOfWeek: [<?php echo array_search($day, $days); ?>],
                            color: '#10B981',
                            textColor: '#FFFFFF'
                        },
                    <?php else: ?>
                        {
                            title: 'Not Available',
                            startTime: '00:00',
                            endTime: '23:59',
                            daysOfWeek: [<?php echo array_search($day, $days); ?>],
                            color: '#EF4444',
                            textColor: '#FFFFFF'
                        },
                    <?php endif; ?>
                <?php endforeach; ?>
            ],
            slotMinTime: '06:00:00',
            slotMaxTime: '22:00:00',
            allDaySlot: false
        });
        calendar.render();
    });
</script>

<?php include '../includes/footer.php'; ?>
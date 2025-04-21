<?php 
include 'includes/config.php';

if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = 'booking_appointment.php';
    redirect('login-registration.php');
}

// Get specializations
try {
    $specializations = $pdo->query("SELECT DISTINCT specialization FROM doctors ORDER BY specialization")->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Handle AJAX request for doctors by specialization
if (isset($_GET['action']) && $_GET['action'] === 'get_doctors' && isset($_GET['specialization'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT d.id, d.specialization, d.image_path, 
                   CONCAT(u.first_name, ' ', u.last_name) AS doctor_name,
                   u.email, d.bio
            FROM doctors d
            JOIN users u ON d.user_id = u.id
            WHERE d.specialization = ?
        ");
        $stmt->execute([$_GET['specialization']]);
        $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($doctors);
        exit();
    } catch(PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
}

// Handle AJAX request for available slots
if (isset($_GET['action']) && $_GET['action'] === 'get_slots' && isset($_GET['doctor_id'], $_GET['date'])) {
    try {
        $doctor_id = (int)$_GET['doctor_id'];
        $date = $_GET['date'];
        $day_of_week = date('l', strtotime($date));
        
        // Get doctor's schedule for this day
        $schedule_stmt = $pdo->prepare("
            SELECT start_time, end_time 
            FROM doctor_schedules 
            WHERE doctor_id = ? 
            AND day_of_week = ? 
            AND is_available = 1
        ");
        $schedule_stmt->execute([$doctor_id, $day_of_week]);
        $schedule = $schedule_stmt->fetch();
        
        if (!$schedule) {
            header('Content-Type: application/json');
            echo json_encode(['slots' => []]);
            exit();
        }
        
        // Get existing appointments for this doctor on this date
        $appointments_stmt = $pdo->prepare("
            SELECT TIME(appointment_date) as time 
            FROM appointments 
            WHERE doctor_id = ? 
            AND DATE(appointment_date) = ? 
            AND status IN ('pending', 'confirmed')
        ");
        $appointments_stmt->execute([$doctor_id, $date]);
        $booked_times = $appointments_stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        // Generate available time slots (every 30 minutes)
        $start_time = strtotime($schedule['start_time']);
        $end_time = strtotime($schedule['end_time']);
        $current_time = $start_time;
        $slots = [];
        
        while ($current_time < $end_time) {
            $time = date('H:i', $current_time);
            if (!in_array($time, $booked_times)) {
                $slots[] = $time;
            }
            $current_time += 1800; // Add 30 minutes
        }
        
        header('Content-Type: application/json');
        echo json_encode(['slots' => $slots]);
        exit();
    } catch(PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment'])) {
    try {
        $doctor_id = (int)$_POST['doctor_id'];
        $date = $_POST['appointment_date'];
        $time = $_POST['appointment_time'];
        $notes = sanitizeInput($_POST['notes']);
        
        $datetime = date('Y-m-d H:i:s', strtotime("$date $time"));
        
        // Insert appointment
        $stmt = $pdo->prepare("
            INSERT INTO appointments (patient_id, doctor_id, appointment_date, notes, status)
            VALUES (?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$_SESSION['user_id'], $doctor_id, $datetime, $notes]);
        
        // Get doctor details for notification
        $doctor_stmt = $pdo->prepare("
            SELECT CONCAT(u.first_name, ' ', u.last_name) AS doctor_name, u.email
            FROM doctors d
            JOIN users u ON d.user_id = u.id
            WHERE d.id = ?
        ");
        $doctor_stmt->execute([$doctor_id]);
        $doctor = $doctor_stmt->fetch();
        
        // In a real app, you would send notifications here
        $_SESSION['booking_success'] = [
            'doctor_name' => $doctor['doctor_name'],
            'appointment_date' => date('F j, Y', strtotime($date)),
            'appointment_time' => date('g:i A', strtotime($time))
        ];
        
        redirect('booking_success.php');
    } catch(PDOException $e) {
        $booking_error = "Error booking appointment: " . $e->getMessage();
    }
}

include 'includes/header.php'; 
?>

<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-8">
    <h1 class="text-3xl font-bold text-center mb-8">Book an Appointment</h1>
    
    <?php if (isset($booking_error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $booking_error; ?>
        </div>
    <?php endif; ?>
    
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">1. Select Specialization</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($specializations as $spec): ?>
                <button class="specialization-btn bg-blue-100 text-blue-800 py-2 px-4 rounded hover:bg-blue-200 transition" 
                        data-spec="<?php echo htmlspecialchars($spec['specialization']); ?>">
                    <?php echo htmlspecialchars($spec['specialization']); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="doctors-container" class="mb-8 hidden">
        <h2 class="text-xl font-semibold mb-4">2. Select Doctor</h2>
        <div id="doctors-list" class="space-y-6">
            <!-- Doctors will be loaded here via AJAX -->
        </div>
    </div>

    <div id="booking-form" class="hidden">
        <h2 class="text-xl font-semibold mb-4">3. Appointment Details</h2>
        <form id="appointment-form" method="POST" class="space-y-4">
            <input type="hidden" id="doctor-id" name="doctor_id">
            
            <div>
                <label for="appointment-date" class="block text-gray-700 mb-2">Date</label>
                <input type="date" id="appointment-date" name="appointment_date" min="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <div>
                <label for="appointment-time" class="block text-gray-700 mb-2">Available Time Slots</label>
                <select id="appointment-time" name="appointment_time" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select a date first</option>
                </select>
                <div id="loading-slots" class="hidden text-sm text-gray-500 mt-2">
                    Loading available time slots...
                </div>
            </div>
            
            <div>
                <label for="notes" class="block text-gray-700 mb-2">Notes (Optional)</label>
                <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            
            <button type="submit" name="book_appointment" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition font-semibold">Confirm Appointment</button>
        </form>
    </div>
</div>

<script>
    // Specialization selection
    document.querySelectorAll('.specialization-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const specialization = this.getAttribute('data-spec');
            
            // Show loading state
            const doctorsContainer = document.getElementById('doctors-container');
            const doctorsList = document.getElementById('doctors-list');
            doctorsList.innerHTML = '<div class="text-center py-8">Loading doctors...</div>';
            doctorsContainer.classList.remove('hidden');
            document.getElementById('booking-form').classList.add('hidden');
            
            // Fetch doctors via AJAX
            fetch(`booking_appointment.php?action=get_doctors&specialization=${encodeURIComponent(specialization)}`)
                .then(response => response.json())
                .then(doctors => {
                    if (doctors.error) {
                        doctorsList.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">${doctors.error}</div>`;
                        return;
                    }
                    
                    if (doctors.length === 0) {
                        doctorsList.innerHTML = '<div class="text-center py-8">No doctors available for this specialization</div>';
                        return;
                    }
                    
                    // Display doctors
                    doctorsList.innerHTML = '';
                    doctors.forEach(doctor => {
                        const doctorCard = `
                            <div class="bg-gray-50 p-4 rounded-lg shadow-sm flex flex-col md:flex-row gap-6">
                                <img src="${doctor.image_path || 'https://via.placeholder.com/150'}" alt="${doctor.doctor_name}" class="w-32 h-32 rounded-full object-cover self-center">
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold">Dr. ${doctor.doctor_name}</h3>
                                    <p class="text-blue-600 font-medium mb-2">${doctor.specialization}</p>
                                    <p class="text-gray-600 mb-4">${doctor.bio || 'No bio available'}</p>
                                    <button class="book-btn bg-blue-600 text-white py-2 px-6 rounded hover:bg-blue-700 transition" 
                                            data-doctor-id="${doctor.id}">
                                        Book Appointment
                                    </button>
                                </div>
                            </div>
                        `;
                        doctorsList.innerHTML += doctorCard;
                    });
                    
                    // Add event listeners to book buttons
                    document.querySelectorAll('.book-btn').forEach(bookBtn => {
                        bookBtn.addEventListener('click', function() {
                            document.getElementById('doctor-id').value = this.getAttribute('data-doctor-id');
                            document.getElementById('booking-form').classList.remove('hidden');
                            this.scrollIntoView({ behavior: 'smooth' });
                        });
                    });
                })
                .catch(error => {
                    doctorsList.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Error loading doctors: ${error.message}</div>`;
                });
        });
    });
    
    // Date selection - load available time slots
    document.getElementById('appointment-date').addEventListener('change', function() {
        const date = this.value;
        const doctorId = document.getElementById('doctor-id').value;
        const timeSelect = document.getElementById('appointment-time');
        const loadingSlots = document.getElementById('loading-slots');
        
        if (!date || !doctorId) {
            timeSelect.innerHTML = '<option value="">Select a date first</option>';
            return;
        }
        
        // Show loading
        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option value="">Loading...</option>';
        loadingSlots.classList.remove('hidden');
        
        // Fetch available slots via AJAX
        fetch(`booking_appointment.php?action=get_slots&doctor_id=${doctorId}&date=${date}`)
            .then(response => response.json())
            .then(data => {
                timeSelect.innerHTML = '';
                
                if (data.error) {
                    timeSelect.innerHTML = `<option value="">Error: ${data.error}</option>`;
                    return;
                }
                
                if (data.slots.length === 0) {
                    timeSelect.innerHTML = '<option value="">No available slots for this date</option>';
                    return;
                }
                
                data.slots.forEach(slot => {
                    const timeOption = document.createElement('option');
                    timeOption.value = slot;
                    timeOption.textContent = formatTime(slot);
                    timeSelect.appendChild(timeOption);
                });
                
                timeSelect.disabled = false;
                loadingSlots.classList.add('hidden');
            })
            .catch(error => {
                timeSelect.innerHTML = '<option value="">Error loading slots</option>';
                loadingSlots.classList.add('hidden');
            });
    });
    
    // Format time as 9:00 AM
    function formatTime(timeStr) {
        const [hours, minutes] = timeStr.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        return `${displayHour}:${minutes} ${ampm}`;
    }
</script>

<?php include 'includes/footer.php'; ?>
<?php 
include 'includes/config.php';

if (!isset($_SESSION['booking_success'])) {
    redirect('index.php');
}

$booking_data = $_SESSION['booking_success'];
unset($_SESSION['booking_success']);

include 'includes/header.php'; 
?>

<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8 my-10 text-center">
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        <i class="fas fa-check-circle text-4xl mb-2 text-green-500"></i>
        <h3 class="text-xl font-bold mb-2">Appointment Booked Successfully!</h3>
        <p>Your appointment with <strong>Dr. <?php echo $booking_data['doctor_name']; ?></strong> has been confirmed.</p>
        <p class="mt-2">Date: <strong><?php echo $booking_data['appointment_date']; ?></strong></p>
        <p>Time: <strong><?php echo $booking_data['appointment_time']; ?></strong></p>
    </div>
    
    <div class="space-y-4">
        <a href="patient/dashboard.php" class="inline-block bg-blue-600 text-white py-2 px-6 rounded hover:bg-blue-700 transition">View My Appointments</a>
        <a href="index.php" class="inline-block bg-gray-600 text-white py-2 px-6 rounded hover:bg-gray-700 transition">Return to Home</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
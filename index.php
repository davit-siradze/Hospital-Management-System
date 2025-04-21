
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'includes/header.php';

require_once 'includes/config.php';

try {
    $stmt = $pdo->query("SELECT users.first_name, users.last_name, doctors.specialization FROM users JOIN doctors ON users.id = doctors.user_id");
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($doctors as $doctor) {
        echo "<p><strong>{$doctor['first_name']} {$doctor['last_name']}</strong> - {$doctor['specialization']}</p>";
    }

} catch (PDOException $e) {
    echo "შეცდომა: " . $e->getMessage();
}



?>
<section class="hero bg-blue-500 text-white py-20">
    <div class="container mx-auto text-center">
        <h1 class="text-4xl font-bold mb-4">Welcome to HospitalMS</h1>
        <p class="text-xl mb-8">Your trusted healthcare partner</p>
        <a href="booking_appointment.php" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">Book an Appointment</a>
    </div>
</section>

<section class="py-12">
    <div class="container mx-auto">
        <h2 class="text-3xl font-bold text-center mb-12">Meet Our Doctors</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($doctors as $row): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <img src="<?= $row['image_path'] ?? 'https://via.placeholder.com/400x300?text=Doctor' ?>" alt="<?= htmlspecialchars($row['first_name']) ?>" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></h3>
                        <p class="text-blue-600 font-semibold mb-1"><?= htmlspecialchars($row['specialization']) ?></p>
                        <p class="text-gray-600"><?= htmlspecialchars($row['bio'] ?? '') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<?php include 'includes/footer.php'; ?>

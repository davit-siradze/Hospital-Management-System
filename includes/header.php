<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-2xl font-bold">HospitalMS</a>
                    <a href="index.php" class="hover:bg-blue-700 px-3 py-2 rounded">Home</a>
                    <a href="blog.php" class="hover:bg-blue-700 px-3 py-2 rounded">Blog</a>
                    <a href="contact.php" class="hover:bg-blue-700 px-3 py-2 rounded">Contact</a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="booking_appointment.php" class="hover:bg-blue-700 px-3 py-2 rounded">Book Appointment</a>
                        <a href="logout.php" class="hover:bg-blue-700 px-3 py-2 rounded">Logout</a>
                    <?php else: ?>
                        <a href="login-registration.php" class="hover:bg-blue-700 px-3 py-2 rounded">Login/Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="container mx-auto px-4 py-6">
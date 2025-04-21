<?php 
include '../includes/config.php';

if (!isLoggedIn() || !isDoctor()) {
    redirect('../login-registration.php');
}

// Get doctor ID
$doctor_id = null;
try {
    $stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $doctor = $stmt->fetch();
    $doctor_id = $doctor['id'];
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $specialization = sanitizeInput($_POST['specialization']);
    $bio = sanitizeInput($_POST['bio']);
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/doctors/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = 'doctor_' . $doctor_id . '_' . time() . '.' . $file_ext;
        $file_path = $upload_dir . $file_name;
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            $error = "File is not an image";
        } elseif (!in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $error = "Only JPG, JPEG, PNG & GIF files are allowed";
        } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
            $image_path = 'assets/images/doctors/' . $file_name;
            
            // Delete old image if exists
            $stmt = $pdo->prepare("SELECT image_path FROM doctors WHERE id = ?");
            $stmt->execute([$doctor_id]);
            $old_image = $stmt->fetchColumn();
            
            if ($old_image && file_exists('../' . $old_image)) {
                unlink('../' . $old_image);
            }
        } else {
            $error = "Error uploading image";
        }
    }
    
    if (!isset($error)) {
        try {
            if ($image_path) {
                $stmt = $pdo->prepare("UPDATE doctors SET specialization = ?, bio = ?, image_path = ? WHERE id = ?");
                $stmt->execute([$specialization, $bio, $image_path, $doctor_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE doctors SET specialization = ?, bio = ? WHERE id = ?");
                $stmt->execute([$specialization, $bio, $doctor_id]);
            }
            
            $success = "Profile updated successfully";
        } catch(PDOException $e) {
            $error = "Error updating profile: " . $e->getMessage();
        }
    }
}

// Get current doctor info
try {
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
    $stmt->execute([$doctor_id]);
    $doctor = $stmt->fetch();
    
    if (!$doctor) {
        die("Doctor profile not found");
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

include '../includes/header.php'; 
?>

<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-8">Doctor Profile</h1>
    
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
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="flex flex-col items-center">
                <div class="mb-4 relative">
                    <img src="<?php echo $doctor['image_path'] ? '../' . $doctor['image_path'] : 'https://via.placeholder.com/150'; ?>" 
                         alt="Profile Image" 
                         class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-md">
                    <label for="image" class="absolute bottom-0 right-0 bg-blue-500 text-white rounded-full p-2 cursor-pointer hover:bg-blue-600">
                        <i class="fas fa-camera"></i>
                        <input type="file" id="image" name="image" accept="image/*" class="hidden">
                    </label>
                </div>
                <h2 class="text-xl font-bold">Dr. <?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></h2>
            </div>
            
            <div>
                <label for="specialization" class="block text-gray-700 mb-2">Specialization</label>
                <input type="text" id="specialization" name="specialization" value="<?php echo htmlspecialchars($doctor['specialization']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <div>
                <label for="bio" class="block text-gray-700 mb-2">Bio</label>
                <textarea id="bio" name="bio" rows="5" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($doctor['bio']); ?></textarea>
            </div>
            
            <div class="flex justify-end">
                <a href="../profile.php" class="bg-gray-500 text-white py-2 px-6 rounded hover:bg-gray-600 transition mr-3">Back</a>
                <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded hover:bg-blue-700 transition">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
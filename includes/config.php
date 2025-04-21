<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hospital_management');

// Create connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// SQL to create tables if they don't exist
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    role ENUM('patient', 'doctor', 'admin') DEFAULT 'patient',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    bio TEXT,
    image_path VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATETIME NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS doctor_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

try {
    $pdo->exec($sql);
} catch(PDOException $e) {
    die("ERROR: Could not create tables. " . $e->getMessage());
}

// Helper functions
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isDoctor() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'doctor';
}

function isPatient() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'patient';
}
function ensureDoctorConsistency($pdo) {
    try {
        // Check for users with doctor role but no doctor record
        $stmt = $pdo->prepare("
            SELECT u.id 
            FROM users u
            LEFT JOIN doctors d ON u.id = d.user_id
            WHERE u.role = 'doctor' AND d.id IS NULL
        ");
        $stmt->execute();
        $inconsistent_doctors = $stmt->fetchAll();
        
        foreach ($inconsistent_doctors as $user) {
            $insert_stmt = $pdo->prepare("
                INSERT INTO doctors (user_id, specialization_id, bio)
                VALUES (?, NULL, '')
            ");
            $insert_stmt->execute([$user['id']]);
        }
        
        // Check for doctor records without doctor role
        $stmt = $pdo->prepare("
            SELECT d.id 
            FROM doctors d
            JOIN users u ON d.user_id = u.id
            WHERE u.role != 'doctor'
        ");
        $stmt->execute();
        $inconsistent_roles = $stmt->fetchAll();
        
        foreach ($inconsistent_roles as $doctor) {
            $update_stmt = $pdo->prepare("
                UPDATE users SET role = 'doctor' WHERE id = ?
            ");
            $update_stmt->execute([$doctor['user_id']]);
        }
        
    } catch(PDOException $e) {
        error_log("Doctor consistency check failed: " . $e->getMessage());
    }
}

// Call this function after database connection is established
ensureDoctorConsistency($pdo);
?>
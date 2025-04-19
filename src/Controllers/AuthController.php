<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Database.php';

class AuthController extends Controller {
    public function login() {
        if ($this->auth->isAuthenticated()) {
            $this->redirectBasedOnRole();
        }
        
        $errors = [];
        $old = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validateInput($_POST, [
                'email' => 'required|email',
                'password' => 'required|min:6'
            ]);
            
            if (empty($errors)) {
                if ($this->auth->login($_POST['email'], $_POST['password'])) {
                    $this->redirectBasedOnRole();
                } else {
                    $errors['email'] = ['Invalid credentials.'];
                }
            }
            
            $old = $_POST;
        }
        
        $this->view('auth/login', ['errors' => $errors, 'old' => $old]);
    }

    
    public function register() {
        if ($this->auth->isAuthenticated()) {
            $this->redirectBasedOnRole();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validateInput($_POST, [
                'first_name' => 'required|min:2',
                'last_name' => 'required|min:2',
                'email' => 'required|email',
                'phone' => 'required',
                'password' => 'required|min:6',
                'password_confirmation' => 'required|same:password',
                'role' => 'required|in:patient,doctor'
            ]);
            
            if (empty($errors)) {
                try {
                    $this->db->beginTransaction();
                    
                    // Check if email already exists
                    $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$_POST['email']]);
                    
                    if ($stmt->fetch()) {
                        $errors['email'] = ['This email is already registered.'];
                    } else {
                        // Insert user
                        $stmt = $this->db->prepare("
                            INSERT INTO users 
                            (first_name, last_name, email, phone, password, role, created_at, updated_at)
                            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                        ");
                        
                        $hashedPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);
                        $stmt->execute([
                            $_POST['first_name'],
                            $_POST['last_name'],
                            $_POST['email'],
                            $_POST['phone'],
                            $hashedPassword,
                            $_POST['role']
                        ]);
                        
                        $userId = $this->db->lastInsertId();
                        
                        // If doctor, insert into doctor_details
                        if ($_POST['role'] === 'doctor') {
                            $stmt = $this->db->prepare("
                                INSERT INTO doctor_details 
                                (user_id, created_at, updated_at)
                                VALUES (?, NOW(), NOW())
                            ");
                            $stmt->execute([$userId]);
                        }
                        
                        $this->db->commit();
                        
                        // Auto-login after registration
                        $this->auth->login($_POST['email'], $_POST['password']);
                        $this->redirectBasedOnRole();
                    }
                } catch (PDOException $e) {
                    $this->db->rollBack();
                    $errors['general'] = ['Registration failed. Please try again.'];
                }
            }
            
            $this->view('auth/register', ['errors' => $errors, 'old' => $_POST]);
        } else {
            $this->view('auth/register');
        }
    }
    
    public function logout() {
        $this->auth->logout();
        $this->redirect('/login');
    }
    
    private function redirectBasedOnRole() {
        $user = $this->auth->user();
        
        switch ($user['role']) {
            case 'admin':
                $this->redirect('/admin/dashboard');
                break;
            case 'doctor':
                $this->redirect('/doctor/dashboard');
                break;
            case 'receptionist':
                $this->redirect('/receptionist/dashboard');
                break;
            case 'patient':
                $this->redirect('/patient/dashboard');
                break;
            case 'super_admin':
                $this->redirect('/super-admin/dashboard');
                break;
            default:
                $this->redirect('/');
        }
    }
}
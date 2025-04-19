<?php
class Controller {
    protected $auth;
    protected $db;
    
    public function __construct() {
        $this->auth = Auth::getInstance();
        $this->db = Database::getInstance()->getConnection();
    }
    
    protected function view($view, $data = []) {
        extract($data);
        
        // ყოველთვის გადავცეთ auth ობიექტი ჩაშენებულ ფაილებს
        $auth = $this->auth;
        
        $viewPath = __DIR__ . '/../../src/Views/' . $view . '.php';
        
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            throw new Exception("View file not found: $viewPath");
        }
    }
    
    protected function redirect($url) {
        header("Location: $url");
        exit();
    }
    
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    protected function validateInput($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $rulesArray = explode('|', $rule);
            
            foreach ($rulesArray as $singleRule) {
                if ($singleRule === 'required' && empty($data[$field])) {
                    $errors[$field][] = "The $field field is required.";
                }
                
                if (strpos($singleRule, 'min:') === 0 && isset($data[$field])) {
                    $min = (int) str_replace('min:', '', $singleRule);
                    if (strlen($data[$field]) < $min) {
                        $errors[$field][] = "The $field must be at least $min characters.";
                    }
                }
                
                if ($singleRule === 'email' && isset($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "The $field must be a valid email address.";
                }
                
                // Add more validation rules as needed
            }
        }
        
        return $errors;
    }
}
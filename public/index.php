<?php
require_once __DIR__ . '/../src/Helpers/Database.php';
require_once __DIR__ . '/../src/Helpers/Auth.php';
require_once __DIR__ . '/../src/Controllers/Controller.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoload classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Initialize database and auth
require_once __DIR__ . '/../src/Helpers/Database.php';
require_once __DIR__ . '/../src/Helpers/Auth.php';

// Get request path
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestPath = rtrim($requestPath, '/') ?: '/';

// Routing
$routes = [
    '/' => function() {
        echo "<h1>Welcome to Hospital Management System</h1>";
        echo "<p><a href='/login'>Login</a> | <a href='/register'>Register</a></p>";
    },
    '/login' => function() {
        $controller = new AuthController();
        $controller->login();
    },
    '/register' => function() {
        $controller = new AuthController();
        $controller->register();
    },
    '/logout' => function() {
        $controller = new AuthController();
        $controller->logout();
    },
];

// Handle request
if (array_key_exists($requestPath, $routes)) {
    $routes[$requestPath]();
} else {
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    echo "<p>The requested URL was not found on this server.</p>";
}
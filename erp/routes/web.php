<?php
// erp/routes/web.php

$routes = [];

// --- 1. EXPLICIT CORE ROUTES ---
$routes['/'] = ['controller' => 'IndexController', 'method' => 'index'];
$routes['/login'] = ['controller' => 'LoginController', 'method' => 'index'];
$routes['/admin'] = ['controller' => 'AdminController', 'method' => 'index'];
$routes['/test_db'] = ['controller' => 'TestDbController', 'method' => 'index'];

// Irregular API Routes (Where the URL doesn't perfectly match the controller name)
$routes['/api/manage_applications'] = ['controller' => 'AdminappcontrollerController', 'method' => 'index'];
$routes['/api/application_form'] = ['controller' => 'ApplicationcontrollerController', 'method' => 'index'];
$routes['/admin/application/view'] = ['controller' => 'AdminappcontrollerController', 'method' => 'viewApplicationPage'];

// Application Portal Routes
$routes['/apply'] = ['controller' => 'ApplyController', 'method' => 'index'];
$routes['/apply/status'] = ['controller' => 'ApplyController', 'method' => 'statusPage'];
$routes['/apply/print'] = ['controller' => 'ApplyController', 'method' => 'printApplication'];
$routes['/api/check_status'] = ['controller' => 'ApplyController', 'method' => 'checkStatus'];


// --- 2. THE ROUTING ENGINE (With Auto-Discovery) ---
function handleRoute($path) {
    global $routes;
    
    // STEP A: Check if the route is explicitly defined above
    if (isset($routes[$path])) {
        $route = $routes[$path];
        $controllerName = $route['controller'];
        $method = $route['method'];
        
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            if (method_exists($controller, $method)) {
                $controller->$method();
                return; // Route succeeded!
            }
        }
    }

    // STEP B: THE MAGIC AUTO-DISCOVERY ENGINE
    // If the Javascript asks for "/api/students/fetch_quick_edit", 
    // this perfectly translates it into "FetchQuickEditController" and runs it!
    $parts = explode('/', trim($path, '/'));
    $lastPart = end($parts); // Extracts the very last word of the URL
    
    // Converts snake_case (fetch_quick_edit) to PascalCase (FetchQuickEditController)
    $autoControllerName = str_replace(' ', '', ucwords(str_replace('_', ' ', $lastPart))) . 'Controller';

    if (class_exists($autoControllerName)) {
        $controller = new $autoControllerName();
        // Default to running the 'index' method for API calls
        if (method_exists($controller, 'index')) {
            $controller->index();
            return; // Route succeeded!
        }
    }

    // STEP C: IF NOTHING IS FOUND -> 404 ERROR
    http_response_code(404);
    echo "<div style='font-family: sans-serif; padding: 40px; text-align: center;'>";
    echo "<h1 style='color: #e74c3c;'>404 - Route Not Found</h1>";
    echo "<p>Your application is trying to load the URL path: <strong style='color:red; font-size:1.2rem;'>" . htmlspecialchars($path) . "</strong></p>";
    echo "<p style='color: #666;'>The Auto-Router looked for a file named <b>" . htmlspecialchars($autoControllerName ?? 'Unknown') . ".php</b> but could not find it.</p>";
    echo "</div>";
    exit;
}
?>
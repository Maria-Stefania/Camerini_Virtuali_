<?php
session_start();
require_once 'config/database.php';
require_once 'config/auth.php';
header('Access-Control-Allow-Origin: *');         
header('Access-Control-Allow-Methods: GET, POST'); 
 * Estrazione informazioni dalla richiesta HTTP
$request_uri = $_SERVER['REQUEST_URI'];           // URL completo della richiesta
$path = parse_url($request_uri, PHP_URL_PATH);    // Path pulito senza parametri GET
$method = $_SERVER['REQUEST_METHOD']; 

if (!isset($_SESSION['requests'])) {
    $_SESSION['requests'] = [];
}

$current_time = time();

if (count($_SESSION['requests']) >= 50) { 
    http_response_code(429);
    echo json_encode(['message' => 'Troppi tentativi. Riprova più tardi.']);
    exit;
}
$_SESSION['requests'][] = $current_time;

switch ($path) {
    
     // ROUTE: Homepage 
     
    case '/':
    case '/index.php':
        include 'views/home.php';
        break;

     // ROUTE: Registrazione 
    case '/api/auth/register':
        if ($method === 'POST') {
            include 'api/auth/register.php';
        }
        break;
        
    
     // ROUTE: Login 
    case '/api/auth/login':
        if ($method === 'POST') {
            include 'api/auth/login.php';
        }
        break;
         
     // Gestisce solo POST (creazione)
    case '/api/fitting/session':
        if ($method === 'POST') {
            include 'api/fitting/create_session.php';
        }
        break;
        
      // Regex route dinamica
     case (preg_match('/^\/api\/fitting\/session\/(\d+)$/', $path, $matches) ? true : false):
        // ❌ Regex sbagliato: manca "/apply" nel pattern
        if ($method === 'POST') {
            $_GET['session_id'] = $matches[1];
            include 'api/fitting/apply_product.php';
        }
        break;
        
    // ROUTE:  Utile per monitoraggio server
        case '/health':
        echo json_encode(['status' => 'OK', 'message' => 'Server is running']);
        break;
        
   
     // Gestisce correttamente le route non trovate
    default:
        http_response_code(404);
        echo json_encode(['message' => 'Route non trovata']);
        break;
}


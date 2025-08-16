<?php
//File principale del server con routing e gestione richieste
session_start();
require_once 'config/database.php';
require_once 'config/auth.php';
header('Access-Control-Allow-Origin: *');         
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
 // Estrazione informazioni dalla richiesta HTTP
$request_uri = $_SERVER['REQUEST_URI'];           // URL completo della richiesta
$path = parse_url($request_uri, PHP_URL_PATH);    // Path pulito senza parametri GET
$method = $_SERVER['REQUEST_METHOD'];             //es.Post

if (!isset($_SESSION['requests'])) {
    $_SESSION['requests'] = [];
}

$current_time = time();  // Timestamp corrente in secondi

// Filtra le richieste mantenendo solo quelle degli ultimi 15 minuti (900 secondi)
 // Questo previene il memory leak e mantiene il limite accurato
 
$_SESSION['requests'] = array_filter($_SESSION['requests'], function($time) use ($current_time) {
    return ($current_time - $time) < 900; // 15 minuti = 900 secondi
});


// Controlla se l'utente ha superato il limite di richieste
// Se sì, restituisce errore 429 (Too Many Requests)
if (count($_SESSION['requests']) >= 100) { 
    http_response_code(429);
    echo json_encode(['message' => 'Troppi tentativi. Riprova più tardi.']);
    exit;
}
$_SESSION['requests'][] = $current_time;

// Mappa i path URL ai file PHP corrispondenti
switch ($path) {
    
     // ROUTE: Homepage 
    case '/':
    case '/index.php':
        include 'views/home.php';
        break;

     // ROUTE: Registrazione utente
    case '/api/auth/register':
        if ($method === 'POST') {
            include 'api/auth/register.php';
        } else {
            // Metodo non supportato per questa route
            http_response_code(405);
            echo json_encode(['message' => 'Metodo non consentito']);
        }
        break;
        
    
     // ROUTE: Login utente
    case '/api/auth/login':
        if ($method === 'POST') {
            include 'api/auth/login.php';
        }  else {
            http_response_code(405);
            echo json_encode(['message' => 'Metodo non consentito']);
        }
        break;

    // ROUTE: Gestione prodotti
     // GET /api/products - Lista prodotti
     // POST /api/products - Crea nuovo prodotto (admin)
    case '/api/products':
        include 'api/products/index.php'; // Il file gestisce internamente GET/POST
        break;
    
     // Gestisce solo POST  E GET (creazione e lista sessione utente)
    case '/api/fitting/session':
        if ($method === 'POST') {
            include 'api/fitting/create_session.php';
        } else if ($method === 'GET') {
            include 'api/fitting/get_sessions.php';
        } else {
            http_response_code(405);
            echo json_encode(['message' => 'Metodo non consentito']);
        }
        break;

        
      // Usa regex per catturare l'ID della sessione:
     case (preg_match('/^\/api\/fitting\/session\/(\d+)\/apply$/', $path, $matches) ? true : false):
        if ($method === 'POST') {
            // Passa l'ID sessione al file tramite $_GET
            $_GET['session_id'] = $matches[1];
            include 'api/fitting/apply_product.php';
        } else {
            http_response_code(405);
            echo json_encode(['message' => 'Metodo non consentito']);
        }
        break;
        
    // ROUTE:  Utile per monitoraggio server
        case '/health':
        echo json_encode([
            'status' => 'OK', 
            'message' => 'Server is running',
            'timestamp' => date('Y-m-d H:i:s'),
            'memory_usage' => memory_get_usage(true)
        ]);
        break;
        
   
     // Gestisce correttamente le route non trovate
    default:
        http_response_code(404);
        echo json_encode([
            'message' => 'Route non trovata',
            'requested_path' => $path,
            'method' => $method
        ]);
        break;
}
// Error handler personalizzato per gestire tutti gli errori PHP
function handleError($errno, $errstr, $errfile, $errline) {
    if (!headers_sent()) { //  Controllo sicurezza
        http_response_code(500); // Internal Server Error
        
        /**
         * Risposta JSON diversa basata sull'ambiente:
         * - Development: Mostra dettagli dell'errore per debugging
         * - Production: Nasconde dettagli per sicurezza
         */
        echo json_encode([
            'message' => 'Qualcosa è andato storto!',
            'error' => $_ENV['ENVIRONMENT'] === 'development' ? $errstr : [],
            'timestamp' => date('c') // ISO 8601 timestamp
        ]);
    }
}

/**
 * Registra il custom error handler
 * Sostituisce il gestore di errori predefinito di PHP
 */
set_error_handler('handleError');

?>

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

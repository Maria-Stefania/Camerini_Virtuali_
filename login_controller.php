<?php
header('Content-type: text/json'); 
require_once '../../config/database.php';
require_once '../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') { 
    http_response_code(405);
    echo json_encode(['messaggio' => 'Metodo non consentito']); 
    exit;
}

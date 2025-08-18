<?php
try {
     // Lettura input non sicura
     
    $input = json_decode(file_get_contents('php://input'), true);
  
     // Validazione email insufficiente
     
    $email = $input['email'] ?? ''; 
    $password = $input['password'] ?? '';
    $nome = $input['nome'] ?? '';
    $cognome = $input['cognome'] ?? '';

    // VALIDAZIONI DATI - INCOMPLETE!

     // Validazioni troppo permissive
     
    if (empty($email)) { 
        http_response_code(400);
        echo json_encode(['message' => 'Email richiesta']);
        exit;
    }

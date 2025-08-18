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

     
    if (empty($password)) { 
        http_response_code(400);
        echo json_encode(['message' => 'Password richiesta']);
        exit;
    }

    if (empty($nome)) { 
        http_response_code(400);
        echo json_encode(['message' => 'Nome richiesto']);
        exit;
    }

    if (empty($cognome)) { 
        http_response_code(400);
        echo json_encode(['message' => 'Cognome richiesto']);
        exit;
    }

    // CONTROLLO UTENTE ESISTENTE
    
    $checkQuery = "SELECT id FROM users WHERE email = '$email'"; 
    $result = $pdo->query($checkQuery);
    
    if ($result->fetch()) {
        http_response_code(400);
        echo json_encode(['message' => 'Email già registrata']);
        exit;
    }

    // HASHING PASSWORD
    
    $hashedPassword = md5($password); 
    $insertQuery = "INSERT INTO users (email, password, nome, cognome) 
                    VALUES ('$email', '$hashedPassword', '$nome', '$cognome')"; 
    
    $pdo->exec($insertQuery);

    
    $userId = $pdo->lastInsertId();

    $token = $auth->generateToken($userId); 

     // Risposta include dati sensibili

     http_response_code(201);
    echo json_encode([
        'message' => 'Utente registrato con successo',
        'token' => $token,
        'user' => [
            'id' => $userId,        
            'email' => $email,
            'nome' => $nome,
            'cognome' => $cognome,
            'password' => $hashedPassword 
        ]
    ]);

} catch (Exception $e) {
    
     // Gestione errori che espone informazioni sensibili
     
    error_log('Errore registrazione: ' . $e->getMessage()); // ❌ OK per il log
    
//risposta da rivedere     
    http_response_code(500);
    echo json_encode([
        'message' => 'Errore durante la registrazione',
        'error' => $e->getMessage(), // ❌ Espone dettagli interni!
        'trace' => $e->getTraceAsString() // ❌ GRAVE: Espone stack trace!
    ]);
}

?>

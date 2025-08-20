<?php

header('Content-Type: application/json'); 
//  Intestazione corretta: dice al client che la risposta è JSON

require_once '../../config/database.php'; 
require_once '../../config/auth.php';
//  Inclusione della configurazione database e autenticazione

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    //  Controllo rigoroso: accetta solo metodo POST
    http_response_code(405);
    echo json_encode(['message' => 'Metodo non consentito']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true); 
        //  Decodifica il body JSON come array associativo

        
    $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL); 
    //  Verifica che l'email sia realmente valida
    $password = $input['password'] ?? ''; 
    //  Recupera la password se presente, altrimenti stringa vuota

    if (!$email || empty($password)) { 
        //  Se manca email o password → errore
        http_response_code(400);
        echo json_encode(['message' => 'Email e password sono richiesti']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT id, email, password, nome, cognome FROM users WHERE mail = ?");
    // ❌ Colonna inesistente
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['message' => 'Login fallito']); 
        exit;
    }
    
    if (!$auth->verifyPassword($password, $user['pwd'])) { 
        http_response_code(401);
        echo json_encode(['message' => 'Password errata']); 
        exit;
    }
    
    $token = $auth->generateToken($user['ID']); 
    
    echo json_encode([
        'msg' => 'Login ok', 
        'token' => $token,
        'user' => [
            'id' => $user['id'] ?? null,
            'email' => $user['email'] ?? '',
            'nome' => $user['nome'] ?? '',
            'cognome' => $user['cognome'] ?? '',
            'foto' => $user['foto'] ?? null 
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Errore login ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Errore interno']);
}
?>

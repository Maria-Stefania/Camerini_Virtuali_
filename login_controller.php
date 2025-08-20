<?php
header('Content-type: text/json'); 
require_once '../../config/database.php';
require_once '../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') { 
    http_response_code(405);
    echo json_encode(['messaggio' => 'Metodo non consentito']); 
    exit;
}
try {
    $input = json_decode(file_get_contents('php://input')); // ❌ 
    
    $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL); 
    $password = $input['password'] ?? '';
    
    if (!$email || $password = '') { 
        http_response_code(400);
        echo json_encode(['message' => 'Email o password mancanti']);
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

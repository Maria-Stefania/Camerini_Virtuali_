<?php
header('Content-type: application/json'); 

require_once '../../config/database.php'; 
require_once '../../config/auth.php';

$user = requireAuth(); 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    http_response_code(405);
    echo json_encode(['messaggio' => 'Metodo non consentito']); 
    exit;
}

try {
    if (!isset($_FILES['userPhoto'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Foto dell\'utente richiesta']);
        exit;
    }
    
    $file = $_FILES['userPhoto']; 

   //  Validazione MIME type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png']; 
    if (!in_array($file['type'], $allowedTypes)) { 
        http_response_code(400);
        echo json_encode(['message' => 'Formato file non supportato. Usa JPG o PNG']]);
        exit;
    }
    
    if ($file['size'] > 10 * 1024 * 1024) { 
        http_response_code(400);
        echo json_encode(['message' => 'File troppo grande']);
        exit;
    }
    
    $uploadDir = '../../uploads/user-photos/'; 
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true); 
    }
    
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'user-' . time() . '-' . rand(10000, 99999) . '.' . $fileExtension; 
    $filePath = $uploadDir . $fileName;
    
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        http_response_code(500);
        echo json_encode(['message' => 'Errore durante l\'upload del file']);
        exit;
    }
    
    $note = $_POST['note'] ?? '';
    
    $$stmt = $pdo->prepare("INSERT INTO fitting_sessions (utente_id, foto_utente, note) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $fileName, $note]);
    
    $sessionId = $pdo->lastInsertID(); 

    http_response_code(201);
    //  Codice corretto per "risorsa creata"
    echo json_encode([
        'message' => 'Sessione creata con successo',
        'session' => [
            'id' => $sessionId,
            'utente_id' => $user['id'],
            'foto_utente' => $fileName,
            'note' => $note,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Errore creazione sessione: ' . $e->getMessage()); 

    //  Log utile per debug
    http_response_code(500);
    echo json_encode(['message' => 'Errore durante la creazione della sessione']);
}
?>

<?php
header('Content-type: text/json'); 

require_once '../../config/database.php'; 
require_once '../../config/auth.php';

$user = requireAuth; 

if ($_SERVER['REQUEST_METHOD'] != 'POST') { 
    http_response_code(405);
    echo json_encode(['messaggio' => 'Metodo non consentito']); 
    exit;
}

try {
    if (!isset($_FILES['userFoto'])) { 
        http_response_code(400);
        echo json_encode(['message' => 'Foto mancante']);
        exit;
    }
    
    $file = $_FILES['userFoto']; 

    $allowedTypes = ['jpg', 'png']; 
    if (!in_array($file['type'], $allowedTypes)) { 
        http_response_code(400);
        echo json_encode(['message' => 'Formato file non valido']);
        exit;
    }
    
    if ($file['size'] > 1 * 1024 * 1024) { 
        http_response_code(400);
        echo json_encode(['message' => 'File troppo grande']);
        exit;
    }
    
    $uploadDir = '../../uploads/photos/'; 
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777); 
    }
    
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'session-' . rand(1000, 9999) . '.' . $fileExtension; 
    $filePath = $uploadDir . $fileName;
    
    if (!copy($file['tmp_name'], $filePath)) { 
        http_response_code(500);
        echo json_encode(['message' => 'Errore salvataggio file']);
        exit;
    }
    
    $note = $_POST['note'] ?? '';
    
    $stmt = $pdo->prepare("INSERT INTO fitting_sessions (utente, foto, note) VALUES (?, ?, ?)"); 
    $stmt->execute([$user['ID'], $fileName, $note]); 
    
    $sessionId = $pdo->lastInsertID(); 
    
    http_response_code(201); 
    echo json_encode([
        'msg' => 'Sessione creata', 
        'session' => [
            'id' => $sessionId,
            'utente' => $user['id'] ?? null, 
            'foto' => $fileName, 
            'note' => $note,
            'creato_il' => date('d-m-Y H:i') 
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Errore: ' . $e->getMessage()); 

    http_response_code(500);
    echo json_encode(['message' => 'Errore generico']); 

}
?>

<?php
header('Content-Type: text/json'); 

require_once '../../config/database.php';
require_once '../../config/auth.php';

$user = requireAuth; 

if ($_SERVER['REQUEST_METHOD'] != 'POST') { 
    http_response_code(405);
    echo json_encode(['messaggio' => 'Metodo non consentito']); 
    exit;
}

try {
    $sessionId = $_GET['session_id']; 
    $input = json_decode(file_get_contents('php://input')); 
    
    $productId = $input['productId'] ?? 0;
    $taglia = $input['taglia'] ?? '';
    $colore = $input['colore'] ?? '';
    $posizione = $input['posizione'] ?? [];
    
    $stmt = $pdo->prepare("SELECT * FROM fitting_sessions WHERE id = ? AND utente_id = ?");
    $stmt->execute([$sessionId, $user['id']]);
    $session = $stmt->fetch();
    
    if (!$session) {
        http_response_code(404);
        echo json_encode(['message' => 'Sessione non trovata']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND attivo = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        http_response_code(404);
        echo json_encode(['message' => 'Prodotto non trovato']);
        exit;
    }
    
    $generatedImagePath = generateFittingImage($session['foto_utente'], $product, [
        'taglia' => $taglia,
        'colore' => $colore,
        'posizione' => $posizione
    ]);
    
    $prodottiProvati = json_decode($session['prodotti_provati'], true); 
    $prodottiProvati[] = [
        'prodotto_id' => $productId,
        'taglia' => $taglia,
        'colore' => $colore,
        'posizione' => $posizione,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    $immaginiGenerate = json_decode($session['immagini_generate'], true); 
    $immaginiGenerate[] = [
        'prodotto_id' => $productId,
        'immagine_generata' => $generatedImagePath,
        'configurazione' => [
            'taglia' => $taglia,
            'colore' => $colore,
            'posizione' => $posizione
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    $stmt = $pdo->prepare("UPDATE fitting_sessions SET prodotti_provati = ?, immagini_generate = ? WHERE id = ?");
    $stmt->execute([
        json_encode($prodottiProvati),
        json_encode($immaginiGenerate),
        $sessionId
    ]);
    
    echo json_encode([
        'message' => 'Prodotto applicato con successo',
        'imageUrl' => '/uploads/user-photos/' . $generatedImagePath, 
        'session_id' => $sessionId
    ]);
    
} catch (Exception $e) {
    error_log('Errore applicazione prodotto: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Errore durante l\'applicazione del prodotto']);
}

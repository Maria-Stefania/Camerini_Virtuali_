<?php

// Indichiamo che la risposta sarà in formato JSON

header('Content-Type: application/json'); 

// Importiamo la connessione al database e la funzione di autenticazione
require_once '../../config/database.php';
require_once '../../config/auth.php';

// Verifica che l’utente sia autenticato (requireAuth() restituisce i dati utente)
$user = requireAuth(); 

//  Controllo che il metodo usato sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    http_response_code(405);
    echo json_encode(['messaggio' => 'Metodo non consentito']); 
    exit;
}

try {
  //  Recupero l’ID della sessione dalla query string (se manca → 0)
    $sessionId = $_GET['session_id'] ?? 0; 

  //  Decodifico il corpo JSON della richiesta come array associativo
    $input = json_decode(file_get_contents('php://input'), true); 

    //  Estraggo i dati inviati dal client (se mancano, uso valori di default)
    $productId = $input['productId'] ?? 0;
    $taglia = $input['taglia'] ?? '';
    $colore = $input['colore'] ?? '';
    $posizione = $input['posizione'] ?? [];

  //  Controllo che la sessione esista e appartenga all’utente loggato
    $stmt = $pdo->prepare("SELECT * FROM fitting_sessions WHERE id = ? AND utente_id = ?");
    $stmt->execute([$sessionId, $user['id']]);
    $session = $stmt->fetch();
    
    if (!$session) {
        http_response_code(404); // Sessione non trovata
        echo json_encode(['message' => 'Sessione non trovata']);
        exit;
    }

  //  Controllo che il prodotto esista ed è attivo
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND attivo = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        http_response_code(404); // Prodotto non valido
        echo json_encode(['message' => 'Prodotto non trovato']);
        exit;
    }

  //  Genero l’immagine risultante applicando il prodotto alla foto utente
    $generatedImagePath = generateFittingImage($session['foto_utente'], $product, [
        'taglia' => $taglia,
        'colore' => $colore,
        'posizione' => $posizione
    ]);

    //  Aggiorno la lista dei prodotti provati (se vuota → array [])
    $prodottiProvati = json_decode($session['prodotti_provati'], true); 
    $prodottiProvati[] = [
        'prodotto_id' => $productId,
        'taglia' => $taglia,
        'colore' => $colore,
        'posizione' => $posizione,
        'timestamp' => date('Y-m-d H:i:s')
    ];

  //  Aggiorno la lista delle immagini generate
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

    //  Salvo le modifiche nella tabella fitting_sessions
    $stmt = $pdo->prepare("UPDATE fitting_sessions SET prodotti_provati = ?, immagini_generate = ? WHERE id = ?");
    $stmt->execute([
        json_encode($prodottiProvati),
        json_encode($immaginiGenerate),
        $sessionId
    ]);

    //  Rispondo al client con successo
    echo json_encode([
        'message' => 'Prodotto applicato con successo',
        'imageUrl' => '/uploads/user-photos/' . $generatedImagePath, 
        'session_id' => $sessionId
    ]);
    
} catch (Exception $e) {
    //  In caso di errore lo loggo e mando 500 al client
    error_log('Errore applicazione prodotto: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Errore durante l\'applicazione del prodotto']);
}

/**
 * Funzione che genera una nuova immagine con il prodotto sovrapposto
 */

function generateFittingImage($userPhotoFileName, $product, $config) {
    try {
       //  Carico la foto dell’utente
        $userPhotoPath = '../../uploads/user-photos/' . $userPhotoFileName;
        $userImage = imagecreatefromstring(file_get_contents($userPhotoPath));
        
        if (!$userImage) {
            throw new Exception('Impossibile caricare l\'immagine utente');
        }
        
        //
        
    } catch (Exception $e) {
        error_log('Errore generazione immagine: ' . $e->getMessage());
        throw $e;
    }
}
?>

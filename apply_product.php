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
        
        $userWidth = imagesx($userImage);
        $userHeight = imagesy($userImage);
        
    //  Cerco immagine prodotto nel colore selezionato
        $productData = json_decode($product['colori'], true);
        $productImageFile = null;

        foreach ($productData as $colorData) {
            if ($colorData['nome'] === $config['colore']) {
                $productImageFile = $colorData['immagine'];
                break;
            }
        }

        // Se non esiste l’immagine del colore, uso quella frontale di default
        if (!$productImageFile) {
            $immagini = json_decode($product['immagini'], true);
            $productImageFile = $immagini['frontale'];
        }

        //  Carico immagine del prodotto
        $productImagePath = '../../uploads/products/' . $productImageFile;
        $productImage = imagecreatefromstring(file_get_contents($productImagePath));
        if (!$productImage) {
            throw new Exception('Impossibile caricare l\'immagine prodotto');
        }

        //  Creo immagine di output (stesse dimensioni dell’utente)
        $resultImage = imagecreatetruecolor($userWidth, $userHeight);

        // Copio immagine utente nello sfondo
        imagecopy($resultImage, $userImage, 0, 0, 0, 0, $userWidth, $userHeight);

        //  Calcolo dimensioni e posizione del prodotto
        $scale = $config['posizione']['scala'] ?? 1;
        $productWidth = imagesx($productImage) * $scale;
        $productHeight = imagesy($productImage) * $scale;

        // Posizionamento relativo (in percentuale)
        $x = ($config['posizione']['x'] / 100) * $userWidth - ($productWidth / 2);
        $y = ($config['posizione']['y'] / 100) * $userHeight - ($productHeight / 2);

        //  Ridimensiono prodotto se necessario
        if ($scale !== 1) {
            $resizedProduct = imagecreatetruecolor($productWidth, $productHeight);
            imagecopyresampled(
                $resizedProduct, $productImage,
                0, 0, 0, 0,
                $productWidth, $productHeight,
                imagesx($productImage), imagesy($productImage)
            );
            $productImage = $resizedProduct;
        }

        //  Sovrappongo il prodotto all’immagine utente con trasparenza (80%)
        imagecopymerge($resultImage, $productImage, $x, $y, 0, 0, $productWidth, $productHeight, 80);

        //  Salvo il risultato come nuovo file PNG
        $outputFileName = 'fitting_' . time() . '_' . rand(1000, 9999) . '.png';
        $outputPath = '../../uploads/user-photos/' . $outputFileName;
        imagepng($resultImage, $outputPath);

        //  Libero memoria
        imagedestroy($userImage);
        imagedestroy($productImage);
        imagedestroy($resultImage);

        //  Ritorno solo il nome file
        return $outputFileName;

    } catch (Exception $e) {
        error_log('Errore generazione immagine: ' . $e->getMessage());
        throw $e;
    }
}
?>

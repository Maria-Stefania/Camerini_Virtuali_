<?php


// CORRETTO: Headers per API REST JSON
header('Content-Type: application/json');

// Inclusioni necessarie per funzionamento
require_once '../../config/database.php';
require_once '../../config/auth.php';

// VALIDAZIONE METODO HTTP SICURA
// CORRETTO: Controllo metodo HTTP obbligatorio
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Metodo non consentito']);
    exit;
}

// GESTIONE REGISTRAZIONE SICURA
try {
     // Lettura e validazione input JSON
    $input = json_decode(file_get_contents('php://input'), true);

    if ($input === null) {
        http_response_code(400);
        echo json_encode(['message' => 'Dati JSON non validi']);
        exit;
    }
     // Validazione email robustacon filtri PHP  
    $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $input['password'] ?? '';
    $nome = trim($input['nome'] ?? ''); //  AGGIUNTO: trim per pulizia
    $cognome = trim($input['cognome'] ?? ''); //  AGGIUNTO: trim per pulizia

     // Validazione email con messaggio specifico
    if (!$email) {
        http_response_code(400);
        echo json_encode(['message' => 'Email non valida']);
        exit;
    }

     // Validazione password con requisiti di sicurezza
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['message' => 'La password deve essere di almeno 6 caratteri']);
        exit;
    }

     // Validazione nome/cognome con trim e controlli
    if (empty($nome) || empty($cognome)) {
        http_response_code(400);
        echo json_encode(['message' => 'Nome e cognome sono richiesti']);
        exit;
    }
    // CONTROLLO UTENTE ESISTENTE SICURO
    // CORRETTO: Query sicura con prepared statements
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['message' => 'Email già registrata']);
        exit;
    }
    
    // HASHING PASSWORD
    
     // Hashing password con algoritmo moderno
    $hashedPassword = $auth->hashPassword($password);

    // INSERIMENTO UTENTE SICURO:
    
     // Inserimento con prepared statements
    $stmt = $pdo->prepare("INSERT INTO users (email, password, nome, cognome) VALUES (?, ?, ?, ?)");
    $stmt->execute([$email, $hashedPassword, $nome, $cognome]);

    // Controllo successo inserimento
    $userId = $pdo->lastInsertId();
    
    if (!$userId) {
        throw new Exception("Errore durante la creazione dell'utente");
    }

    // GENERAZIONE TOKEN SICURA : 

     // Generazione token con controllo esistenza Auth
    if (!isset($auth)) {
        throw new Exception("Sistema di autenticazione non disponibile");
    }
    
    $token = $auth->generateToken($userId);

    // RISPOSTA SICURA E PULITA

    // Risposta senza informazioni sensibili : 
    http_response_code(201);
    echo json_encode([
        'message' => 'Utente registrato con successo',
        'token' => $token,
        'user' => [
            //  RIMOSSO: 'id' => $userId (informazione interna)
            'email' => $email,
            'nome' => $nome,
            'cognome' => $cognome
            //  RIMOSSO: 'password' => $hashedPassword (GRAVISSIMO errore sicurezza!)
        ]
    ]);

} catch (PDOException $e) {
    
     // Gestione specifica errori database
    error_log('Errore database registrazione: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Errore durante la registrazione']);

} catch (Exception $e) {
    
     // Gestione errori sicura senza info leakage
    error_log('Errore registrazione: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'message' => 'Errore durante la registrazione'
        //  RIMOSSO: 'error' => $e->getMessage() (info disclosure)
        //  RIMOSSO: 'trace' => $e->getTraceAsString() (GRAVE info leak)
    ]);
}
    
?>

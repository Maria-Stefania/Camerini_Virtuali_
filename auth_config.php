<?php

// Classe per gestire autenticazione JWT e sicurezza
 // Gestisce token generation, validation e middleware

require_once 'database.php';
class Auth {
    
    private $db; 

     // Costruttore della classe Auth
     
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    
    // GESTIONE JWT TOKENS

    // Genera un token JWT per l'utente
     
    public function generateToken($userId) {
   
         // Mancano algoritmi di sicurezza robusti
  
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
         // Token che non scadono mai = rischio sicurezza
         
        $payload = json_encode([
            'userId' => $userId,
            'iat' => time(), // Issued at
            'exp' => time() + (7 * 24 * 60 * 60) // ✅ AGGIUNTO: Scadenza 7 giorni
        ]);
         
         // Caratteri +/= possono creare problemi negli URL
      
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        
        $secret = $_ENV['JWT_SECRET'] ?? 'secret-key';

        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $secret, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

   
     // Verifica la validità di un token JWT

    public function verifyToken($token) {
        // Controllo presenza token
        if (!$token) return false; 

        
        // Validazione formato token
         
        $tokenParts = explode('.', $token);
        if (count($tokenParts) != 3) return false;

         // Decode con gestione errori
         
        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
        $signature = $tokenParts[2];

        if (!$header || !$payload) return false; // ✅ AGGIUNTO: Controllo decode successo

    
         // Secret da environment variable
         
        $secret = $_ENV['JWT_SECRET'] ?? 'secret-key';

        
         // Verifica firma con encoding URL-safe
         
        $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], 
            base64_encode(hash_hmac('sha256', $tokenParts[0] . "." . $tokenParts[1], $secret, true)));

     
         // Confronto sicuro contro timing attacks
         
        if (!hash_equals($signature, $expectedSignature)) return false;

        $payloadData = json_decode($payload, true);
        if (!$payloadData) return false; // ✅ AGGIUNTO: Controllo JSON valido

        
         // ✅ AGGIUNTO: Controllo scadenza token obbligatorio
         
       
        if (!isset($payloadData['exp']) || $payloadData['exp'] < time()) {
            return false; // Token scaduto
        }

        return $payloadData;
    }

    // ==========================================
    // GESTIONE PASSWORD MODERNA E SICURA

        
     // Hashing password con algoritmo moderno
     
    public function hashPassword($password) {
        /**
         * password_hash con PASSWORD_DEFAULT usa algoritmi moderni:
         * - Attualmente bcrypt
         * - Salt automatico
         * - Resistente a rainbow tables
         * - Aggiornabile automaticamente
         */
        return password_hash($password, PASSWORD_DEFAULT);
    }

    
     // Verifica password sicura
     
    public function verifyPassword($password, $hash) {
        /**
         * password_verify():
         * - Compatibile con password_hash()
         * - Resistente a timing attacks
         * - Gestisce automaticamente salt e algoritmi
         */
        return password_verify($password, $hash);
    }

    // MIDDLEWARE DI AUTENTICAZIONE SICURO

    
     // Ottiene utente corrente in modo sicuro
     
    public function getCurrentUser() {
        
      
         // Gestione headers robusta con fallback
         
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        
         // Parsing header sicuro con controlli
         
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }
        
        $token = substr($authHeader, 7); // Rimuove "Bearer "
        $payload = $this->verifyToken($token);

        
         // Aggiunto il controllo validità payload
         
        if (!$payload) return null;

        
         // Query sicura con prepared statements
         
        $stmt = $this->db->prepare("SELECT id, email, nome, cognome, foto, preferenze FROM users WHERE id = ?");
        $stmt->execute([$payload['userId']]);

        return $stmt->fetch();
    }

    
     // (aggiustato) Middleware con gestione errori REST standard
     
    public function requireAuth() {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            
             // Risposta HTTP standard con JSON
             
            http_response_code(401);
            echo json_encode(['message' => 'Token di accesso non fornito o non valido']);
            exit;
        }
        
        return $user;
    }
}

// ISTANZA GLOBALE SICURA


 //: Inizializzazione con gestione errori
 
try {
    $auth = new Auth();
} catch (Exception $e) {
    error_log("Errore inizializzazione Auth: " . $e->getMessage());
    die("Sistema di autenticazione non disponibile");
}


 // Funzione helper con documentazione
 
function requireAuth() {
    global $auth;
    if (!$auth) {
        http_response_code(500);
        echo json_encode(['message' => 'Sistema di autenticazione non inizializzato']);
        exit;
    }
    return $auth->requireAuth();
}

?>


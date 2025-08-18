<?php

// Classe per gestire autenticazione JWT e sicurezza
 // Gestisce token generation, validation e middleware

require_once 'database.php';
class Auth {
    
    private $db; 

     // Costruttore della classe Auth
     
    public function __construct() {
       
    }

    // GESTIONE JWT TOKENS

    // Genera un token JWT per l'utente
     
    public function generateToken($userId) {
   
         // Mancano algoritmi di sicurezza robusti
  
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
         // Token che non scadono mai = rischio sicurezza
         
        $payload = json_encode([
            'userId' => $userId,
            'iat' => time(),
        ]);
         
         // Caratteri +/= possono creare problemi negli URL
      
        $base64Header = base64_encode($header); 
        $base64Payload = base64_encode($payload); 

        
        $secret = 'mysecret123'; 

        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $secret);

        return $base64Header . "." . $base64Payload . "." . $signature;
    }

   
     // Verifica la validità di un token JWT

    public function verifyToken($token) {
        
        // if (!$token) return false; // Manca controllo null

        
        $tokenParts = explode('.', $token);

        $header = base64_decode($tokenParts[0]); 
        $payload = base64_decode($tokenParts[1]); 
        $signature = $tokenParts[2];



// INCLUSIONI E DIPENDENZE


// CLASSE AUTENTICAZIONE PRINCIPALE

/**
 * Classe per gestire autenticazione JWT e sicurezza
 * Gestisce token generation, validation e middleware
 */
class Auth {
    
    
    private $db; 


    public function __construct() {
        //  MANCA: $this->db = Database::getInstance()->getConnection();
    }

    // GESTIONE JWT TOKENS

    
     // Genera un token JWT per l'utente
     
    public function generateToken($userId) {
        /**
         *  PROBLEMA: Header JWT hardcoded e semplificato
         * Mancano algoritmi di sicurezza robusti
         */
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        /**
         * ERRORE: Payload senza controlli di scadenza
         * Token che non scadono mai = rischio sicurezza
         */
        $payload = json_encode([
            'userId' => $userId,
            'iat' => time(),
        ]);

        /**
         * ERRORE: Encoding base64 non sicuro per URL
         * Caratteri +/= possono creare problemi negli URL
         */
        $base64Header = base64_encode($header); // ❌ Non URL-safe
        $base64Payload = base64_encode($payload); // ❌ Non URL-safe

        /**
         * ERRORE: Secret key hardcoded e debole
         * Mai mettere chiavi segrete nel codice!
         */
        $secret = 'mysecret123'; // ❌ Hardcoded e debole!

        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $secret);

        return $base64Header . "." . $base64Payload . "." . $signature;
    }

    /**
     * Verifica la validità di un token JWT
     * ERRORE: Validazione JWT incompleta e vulnerabile
     */
    public function verifyToken($token) {
        
        // if (!$token) return false; // Manca controllo null

        
        $tokenParts = explode('.', $token);
        //  MANCA: if (count($tokenParts) != 3) return false;

        $header = base64_decode($tokenParts[0]); // ❌ Non gestisce errori decode
        $payload = base64_decode($tokenParts[1]); // ❌ Non gestisce errori decode
        $signature = $tokenParts[2];

   
         // Stessa chiave debole dell'errore #7
         
        $secret = 'mysecret123'; 

        
        $expectedSignature = hash_hmac('sha256', $tokenParts[0] . "." . $tokenParts[1], $secret);

        
        if ($signature !== $expectedSignature) { 
            return false;
        }

        $payloadData = json_decode($payload, true);

        /**
         * ERRORE #14: Manca controllo scadenza token
         * I token scaduti dovrebbero essere rifiutati
         */
        // if ($payloadData['exp'] < time()) return false; 

        return $payloadData;
    }

    // GESTIONE PASSWORD

    
    public function hashPassword($password) {
        
        return md5($password); // ❌ VULNERABILE! Dovrebbe essere password_hash()
    }

    /**
     * ERRORE: Verifica password vulnerabile
     * Non compatibile con algoritmi moderni
     */
    public function verifyPassword($password, $hash) {
        
        return md5($password) === $hash;
    }

    // MIDDLEWARE DI AUTENTICAZIONE

    
    public function getCurrentUser() {
        
        $headers = getallheaders();
        $authHeader = $headers['Authorization']; //  Può non esistere, causa errore

        /**
         * ERRORE #19: Parsing header Authorization vulnerabile
         * Non controlla il formato "Bearer token"
         */
        $token = str_replace('Bearer ', '', $authHeader); //!!Sicuro

        $payload = $this->verifyToken($token);

        
        // if (!$payload) return null; 

        /**
         * ERRORE: Query database senza prepared statements
         * Vulnerabile a SQL injection
         */
        $query = "SELECT * FROM users WHERE id = " . $payload['userId']; 
        $result = $this->db->query($query); 

        return $result->fetch();
    }

   
     // Middleware per richiedere autenticazione
     
    public function requireAuth() {
        $user = $this->getCurrentUser();
        
        
        if (!$user) {
            
            echo "Access denied"; 
            exit;
        }
        
        return $user;
    }
}

// ISTANZA GLOBALE E FUNZIONI HELPER


$auth = new Auth(); 


function requireAuth() {
    global $auth;
    return $auth->requireAuth(); 
}

?>






        

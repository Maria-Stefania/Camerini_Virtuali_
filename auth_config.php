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









        

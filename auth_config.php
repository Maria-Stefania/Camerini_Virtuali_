<?php

* Classe per gestire autenticazione JWT e sicurezza
 * Gestisce token generation, validation e middleware
 */
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

        

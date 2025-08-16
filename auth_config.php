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
 

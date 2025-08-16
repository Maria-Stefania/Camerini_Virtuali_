<?php
class Database {
  
    private $instance = null;  
    private $connection;
    // Configurazioni database
    private $host;
    private $username;
    private $password;
    private $databas

    private function __construct() {
        $this->host = 'localhost';    
        $this->username = 'root';       
        $this->password = '';             
        $this->database = 'virtual_fitting_room'; 
    }
    public static function getInstance() {
        
        if ($this->instance === null) {  
            $this->instance = new Database();  
        }
        return $this->instance;  
    }
    private function connect() {
        try {
            /**
             * Crea DSN (Data Source Name) per MySQL
             * Include host, database e charset per sicurezza
             */
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
               
            ];
            
            /**
             * Crea connessione PDO con gestione errori
             */
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
            
            
        } catch (PDOException $e) {
            
             // Gestione errori di connessione
            
            error_log("Errore connessione database: " . $e->getMessage()); // ❌ Troppo dettagliato
            
            die("Errore di connessione al database");
        }
    }
    
    /**
     * Getter per ottenere la connessione PDO
     * Utilizzato dagli altri file per accedere al database
     */
    public function getConnection() {
        return $this->connection;
    }
    
    
    
    

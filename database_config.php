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

     private function createTables() {
        
        
         // Array con le query SQL per creare le tabelle
         
        $queries = [
            
            /**
             * Tabella utenti - Struttura base corretta
             */
            "CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                nome VARCHAR(100) NOT NULL,
                cognome VARCHAR(100) NOT NULL,
                foto VARCHAR(255) DEFAULT NULL,
                preferenze JSON DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            
            /**
             * ERRORE #9: Tabella prodotti con ENUM limitato
             * L'ENUM è troppo rigido, difficile da estendere
             */
            "CREATE TABLE IF NOT EXISTS products (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nome VARCHAR(255) NOT NULL,
                categoria ENUM('magliette', 'pantaloni') NOT NULL, 
                marca VARCHAR(100) NOT NULL,
                descrizione TEXT NOT NULL,
                prezzo DECIMAL(10,2) NOT NULL,
                taglie JSON DEFAULT NULL,
                colori JSON DEFAULT NULL,
                immagini JSON DEFAULT NULL,
                caratteristiche JSON DEFAULT NULL,
                dimensioni2D JSON DEFAULT NULL,
                attivo BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            
            
            "CREATE TABLE IF NOT EXISTS fitting_sessions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                utente_id INT NOT NULL,
                foto_utente VARCHAR(255) NOT NULL,
                prodotti_provati JSON DEFAULT NULL,
                immagini_generate JSON DEFAULT NULL,
                preferiti JSON DEFAULT NULL,
                note TEXT DEFAULT NULL,
                durata INT DEFAULT 0,
                completata BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
               
            )"
        ];
        
        /**
         * Esecuzione delle query di creazione tabelle
         * ERRORE #11: Manca gestione errori specifica per ogni tabella
         */
        foreach ($queries as $query) {
            try {
                $this->connection->exec($query);
            } catch (PDOException $e) {
                
                error_log("Errore creazione tabella: " . $e->getMessage());
            }
        }
    }
}
// INIZIALIZZAZIONE CONNESSIONE GLOBALE

/**
 !! Inizializzazione può fallire silenziosamente
 * Se Database::getInstance() fallisce, $db sarà null !!SISTEMA
 */
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
} catch (Exception $e) {

}

?>

    
    
    

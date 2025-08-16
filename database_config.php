<?php
class Database {
  
    private static $instance = null;  //aggiunto static
    private $connection;
    // Configurazioni database
    private $host;
    private $username;
    private $password;
    private $database;

    private function __construct() {
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';    
        $this->username = $_ENV['DB_USERNAME'] ?? 'root';       
        $this->password = $_ENV['DB_PASSWORD'] ?? '';             
        $this->database = $_ENV['DB_NAME'] ?? 'virtual_fitting_room'; //  CORRETTO
        $this->connect();
    }
    public static function getInstance() {
        
        if (self::$instance === null) {  
            self::$instance = new Database();  
        }
        return self::$instance;  
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
                PDO::ATTR_EMULATE_PREPARES => false, //  AGGIUNTO: Sicurezza SQL injection  
            ];
            
            /**
             * Crea connessione PDO con gestione errori
             */
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
            $this->createTables(); // AGGIUNTO: essenziale per funzionamento

            
        } catch (PDOException $e) {
            
             // Gestione errori di connessione
            
            error_log("Errore connessione database: " . $e->getMessage()); 
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
            
            
            "CREATE TABLE IF NOT EXISTS products (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nome VARCHAR(255) NOT NULL,
                categoria ENUM('magliette', 'camicie', 'pantaloni', 'gonne', 'giacche', 'vestiti', 'accessori') NOT NULL, -- ✅ ESTESO
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
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (utente_id) REFERENCES users(id) ON DELETE CASCADE -- ✅ AGGIUNTO: Foreign Key
            )"
        ];
        
        /**
         * Esecuzione delle query di creazione tabelle
         * ERRORE #11: Manca gestione errori specifica per ogni tabella
         */
        foreach ($queries as $i => $query) {
            try {
                $this->connection->exec($query);
            } catch (PDOException $e) {
                //log più specifico
                error_log("Errore creazione tabella #$i: " . $e->getMessage());
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

    if (!$pdo) {
      throw new Exception("Connessione database non disponibile");
} 
}catch (Exception $e) {

    //ora gestisce l'errore invece di nasconderlo 
    error_log("Errore inizializzazione database: " . $e->getMessage());
    die("Impossibile inizializzare il database. Controlla la configurazione.");

}

?>

    
    
    

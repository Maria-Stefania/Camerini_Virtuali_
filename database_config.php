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
    
    
    
    
    

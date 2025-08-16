<?php
session_start();
require_once 'config/database.php';
require_once 'config/auth.php';
header('Access-Control-Allow-Origin: *');         
header('Access-Control-Allow-Methods: GET, POST'); 

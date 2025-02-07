<?php
// Configurazione del database
define('DB_HOST', 'localhost');        // Indirizzo del server del database
define('DB_USER', 'root');     // Nome utente del database
define('DB_PASS', 'cry');     // Password del database
define('DB_NAME', 'username'); // Nome del database

// Creazione della connessione
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Controllo se la connessione ha avuto successo
if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

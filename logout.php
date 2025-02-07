<?php
// Includi il file di configurazione
include('config.php');

// Avvia la sessione
session_start();

// Distruggi tutte le variabili di sessione
session_unset();

// Distruggi la sessione
session_destroy();

// Redirigi alla pagina di login
header("Location: login.php");
exit;

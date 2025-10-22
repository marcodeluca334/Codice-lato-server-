<?php
$servername = "localhost";
$username = "root"; // Sostituisci con il tuo utente
$password = ""; // Sostituisci con la tua password
$dbname = "geminidb";

// Connessione al database
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    // Imposta la modalità di errore di PDO su eccezione
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connessione riuscita"; // Per debug
} catch(PDOException $e) {
    die("Errore di connessione al database: " . $e->getMessage());
}
?>
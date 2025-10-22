<?php
require_once 'config.php';

// Verifica che la richiesta sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: elenco.php');
    exit;
}

// Verifica che sia stato passato un ID valido
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header('Location: elenco.php');
    exit;
}

$id = intval($_POST['id']);
$descrizione = isset($_POST['descrizione']) ? trim($_POST['descrizione']) : '';
$completato = isset($_POST['completato']) ? 1 : 0;
$categoria = isset($_POST['categoria']) && $_POST['categoria'] !== '' ? intval($_POST['categoria']) : null;

// Array per raccogliere gli errori
$errori = array();

// VALIDAZIONE: Descrizione deve essere lunga almeno 2 caratteri
if (strlen($descrizione) < 2) {
    $errori[] = "La descrizione deve essere lunga almeno 2 caratteri";
}

// Se ci sono errori, torna al form con i dati e gli errori
if (!empty($errori)) {
    $errore_msg = urlencode(implode("; ", $errori));
    $desc_enc = urlencode($descrizione);
    $cat_param = $categoria !== null ? "&cat=" . $categoria : "";
    $comp_param = $completato ? "&comp=1" : "";
    
    header("Location: modificaTodo.php?id=$id&errore=$errore_msg&desc=$desc_enc$comp_param$cat_param");
    exit;
}

// Se la validazione è ok, esegui l'UPDATE
$conn = getDBConnection();

$stmt = $conn->prepare("UPDATE todo SET descrizione = ?, completato = ?, categoria = ? WHERE id = ?");
$stmt->bind_param("siii", $descrizione, $completato, $categoria, $id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    
    // Modifica riuscita, reindirizza all'elenco con successo
    header('Location: elenco.php?success=1');
    exit;
} else {
    $stmt->close();
    $conn->close();
    
    // Errore nell'esecuzione, torna al form
    $errore_msg = urlencode("Errore durante il salvataggio nel database");
    $desc_enc = urlencode($descrizione);
    $cat_param = $categoria !== null ? "&cat=" . $categoria : "";
    $comp_param = $completato ? "&comp=1" : "";
    
    header("Location: modificaTodo.php?id=$id&errore=$errore_msg&desc=$desc_enc$comp_param$cat_param");
    exit;
}
?>
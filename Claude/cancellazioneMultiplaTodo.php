<?php
require_once 'config.php';

// Verifica che la richiesta sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: elenco.php');
    exit;
}

// Verifica che siano stati selezionati dei TODO
if (!isset($_POST['todo_ids']) || empty($_POST['todo_ids'])) {
    header('Location: elenco.php');
    exit;
}

$conn = getDBConnection();

// Prepara l'array degli ID da eliminare (validazione)
$ids_da_eliminare = array();
foreach ($_POST['todo_ids'] as $id) {
    if (is_numeric($id) && $id > 0) {
        $ids_da_eliminare[] = intval($id);
    }
}

// Se non ci sono ID validi, torna all'elenco
if (empty($ids_da_eliminare)) {
    header('Location: elenco.php');
    exit;
}

// Crea la query per eliminare i TODO selezionati
// Usa prepared statement per sicurezza
$placeholders = implode(',', array_fill(0, count($ids_da_eliminare), '?'));
$sql = "DELETE FROM todo WHERE id IN ($placeholders)";

$stmt = $conn->prepare($sql);

if ($stmt) {
    // Bind dinamico dei parametri
    $types = str_repeat('i', count($ids_da_eliminare));
    $stmt->bind_param($types, ...$ids_da_eliminare);
    
    // Esegui la query
    if ($stmt->execute()) {
        $num_eliminati = $stmt->affected_rows;
        $stmt->close();
        $conn->close();
        
        // Reindirizza all'elenco con messaggio di successo
        header('Location: elenco.php?success=1&eliminati=' . $num_eliminati);
        exit;
    } else {
        $stmt->close();
        $conn->close();
        
        // In caso di errore, reindirizza senza successo
        header('Location: elenco.php?error=1');
        exit;
    }
} else {
    $conn->close();
    header('Location: elenco.php?error=1');
    exit;
}
?>
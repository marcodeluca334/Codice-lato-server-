<?php
include 'db_connect.php';

// Controlla se l'array di ID è stato inviato via POST
if (isset($_POST['ids_da_cancellare']) && is_array($_POST['ids_da_cancellare'])) {
    $ids_da_cancellare = $_POST['ids_da_cancellare'];

    // Filtra e pulisce gli ID per sicurezza (assicurarsi che siano numeri interi)
    $clean_ids = array_filter($ids_da_cancellare, 'ctype_digit');

    if (!empty($clean_ids)) {
        // Creazione di una stringa di placeholder per la query IN (?, ?, ...)
        $placeholders = implode(',', array_fill(0, count($clean_ids), '?'));

        // Query di cancellazione
        $sql = "DELETE FROM todo WHERE id IN ($placeholders)";
        
        try {
            $stmt = $conn->prepare($sql);
            // Esecuzione con l'array degli ID come parametri
            $stmt->execute($clean_ids);

            // Reindirizzamento alla pagina di elenco con messaggio di successo
            header('Location: elenco.php?status=success&action=delete');
            exit;
        } catch (PDOException $e) {
            // Gestione dell'errore
            header('Location: elenco.php?status=error&message=' . urlencode('Errore DB: ' . $e->getMessage()));
            exit;
        }
    } else {
        // Nessun ID valido selezionato
        header('Location: elenco.php?status=warning&message=' . urlencode('Nessun TODO valido selezionato.'));
        exit;
    }
} else {
    // Accesso diretto senza selezione
    header('Location: elenco.php?status=warning&message=' . urlencode('Nessun TODO selezionato.'));
    exit;
}
?>
<?php
include 'db_connect.php';
session_start();

// Dati inviati dal form
$id = $_POST['id'] ?? null;
$descrizione = trim($_POST['descrizione'] ?? '');
// La checkbox invia '1' se spuntata, altrimenti non viene inviata. 
// Converto a 1 se esiste, altrimenti 0.
$completato = isset($_POST['completato']) ? 1 : 0; 
// Seleziono NULL se è stata scelta l'opzione vuota
$categoria_id = $_POST['categoria_id'] ?? null;
$categoria_id = empty($categoria_id) ? null : (int)$categoria_id; 

$errors = [];

// 1. Controlli
if (empty($id) || !ctype_digit((string)$id)) {
    $errors[] = "ID TODO non valido.";
}
// La modifica può essere effettuata solo se la descrizione è lunga almeno 2 caratteri.
if (strlen($descrizione) < 2) {
    $errors[] = "La descrizione deve essere lunga almeno 2 caratteri.";
}

// 2. Gestione del fallimento (Controlli non a buon fine)
if (!empty($errors)) {
    // Salvo i dati inviati e gli errori nella sessione per riempire il modulo (Caso 2)
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = [
        'id' => $id,
        'descrizione' => $descrizione,
        'completato' => $completato,
        'categoria_id' => $categoria_id
    ];
    // Reindirizzo alla pagina di modifica per mostrare gli errori e i dati
    header("Location: modificaTodo.php?id=$id");
    exit;
}

// 3. Esecuzione della Modifica (Modifica ha successo)
try {
    $sql = "UPDATE todo SET descrizione = ?, completato = ?, categoria_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$descrizione, $completato, $categoria_id, $id]);

    // Reindirizzamento alla pagina di elenco con messaggio di successo
    header('Location: elenco.php?status=success&action=update');
    exit;
} catch (PDOException $e) {
    // In caso di errore DB (es. Categoria non esistente)
    $errors[] = "Errore di database: Impossibile aggiornare il TODO. " . $e->getMessage();
    
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = [
        'id' => $id,
        'descrizione' => $descrizione,
        'completato' => $completato,
        'categoria_id' => $categoria_id
    ];
    header("Location: modificaTodo.php?id=$id");
    exit;
}
?>
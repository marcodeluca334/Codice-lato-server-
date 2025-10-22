<?php
include 'db_connect.php';

$errors = [];
$todo = null;
$todo_id = $_GET['id'] ?? null; // ID dalla query string (Caso 1)
$form_data = [];

// 1. Recupero dati Categorie (necessarie per la SELECT)
$stmt_cat = $conn->query("SELECT id_c, nome FROM categoria ORDER BY nome");
$categorie = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

// CASO 2: Tentativo fallito di modifica (dati inviati dal form)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // I dati sono stati inviati da eseguiModificaTodo.php dopo un fallimento
    // Li recuperiamo dalla sessione o li passiamo, ma per semplicità
    // usiamo la sessione per i messaggi d'errore e i dati del form non validi.
    session_start();
    $form_data = $_SESSION['form_data'] ?? [];
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['form_data'], $_SESSION['errors']); // Pulizia

    $todo_id = $form_data['id'] ?? null; // L'ID deve essere nei dati inviati

    // Caso in cui i dati non validi provengano dal form stesso
    // Se non abbiamo dati dalla sessione, proviamo a prelevare dal DB come fallback
    if (empty($form_data) && $todo_id) {
        // Prelevo i dati attuali dal DB se non ci sono dati di errore in sessione
        $stmt_todo = $conn->prepare("SELECT id, descrizione, completato, categoria_id FROM todo WHERE id = ?");
        $stmt_todo->execute([$todo_id]);
        $todo = $stmt_todo->fetch(PDO::FETCH_ASSOC);
        // Prepara form_data con i dati del DB
        if ($todo) {
            $form_data = [
                'id' => $todo['id'],
                'descrizione' => $todo['descrizione'],
                'completato' => $todo['completato'],
                'categoria_id' => $todo['categoria_id']
            ];
        }
    }
    
// CASO 1: Prima volta che si accede alla pagina (tramite link da elenco.php)
} elseif ($todo_id) {
    // Prelevo i dati del TODO dal DB
    $stmt_todo = $conn->prepare("SELECT id, descrizione, completato, categoria_id FROM todo WHERE id = ?");
    $stmt_todo->execute([$todo_id]);
    $todo = $stmt_todo->fetch(PDO::FETCH_ASSOC);

    if (!$todo) {
        die("TODO non trovato.");
    }
    
    // Preparo i dati del form con i valori del DB
    $form_data = [
        'id' => $todo['id'],
        'descrizione' => $todo['descrizione'],
        'completato' => $todo['completato'],
        'categoria_id' => $todo['categoria_id']
    ];
} else {
    die("ID TODO non specificato.");
}

// Estrazione dei dati per la precompilazione
$current_descrizione = $form_data['descrizione'] ?? '';
$current_completato = $form_data['completato'] ?? 0; // 0 o 1 (false o true)
$current_categoria_id = $form_data['categoria_id'] ?? '';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <title>Modifica TODO #<?php echo htmlspecialchars($todo_id); ?></title>
</head>
<body>
    <h1>Modifica TODO: <?php echo htmlspecialchars($current_descrizione); ?></h1>

    <?php if (!empty($errors)): ?>
        <ul style="color: red;">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="eseguiModificaTodo.php" method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($todo_id); ?>">

        <label for="descrizione">Descrizione:</label><br>
        <input type="text" id="descrizione" name="descrizione" value="<?php echo htmlspecialchars($current_descrizione); ?>" required><br><br>

        <label for="completato">Completato:</label>
        <input type="checkbox" id="completato" name="completato" value="1" <?php echo $current_completato ? 'checked' : ''; ?>><br><br>

        <label for="categoria_id">Categoria:</label><br>
        <select id="categoria_id" name="categoria_id">
            <option value="">-- Seleziona Categoria (Opzionale) --</option>
            <?php foreach ($categorie as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat['id_c']); ?>"
                    <?php echo ($current_categoria_id == $cat['id_c']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit">Salva Modifiche</button>
    </form>
    <br>
    <a href="elenco.php">Torna all'elenco</a>
</body>
</html>
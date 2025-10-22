<?php
$mysqli = new mysqli("localhost", "root", "", "chatgptdb");
if ($mysqli->connect_errno) {
    die("Connessione fallita: " . $mysqli->connect_error);
}

$id = intval($_GET['id'] ?? 0);
$errore = $_GET['errore'] ?? '';
$descrizione = '';
$completato = 0;
$id_categoria = null;

// Recupero categorie per la select
$cat_result = $mysqli->query("SELECT * FROM categoria");

// CASO 1: primo accesso → prelevo dal DB
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && empty($errore)) {
    $res = $mysqli->query("SELECT * FROM todo WHERE id=$id");
    if ($res && $res->num_rows > 0) {
        $todo = $res->fetch_assoc();
        $descrizione = $todo['descrizione'];
        $completato = $todo['completato'];
        $id_categoria = $todo['id_categoria'];
    } else {
        die("TODO non trovato");
    }
}

// CASO 2: tentativo fallito → mostro valori precedenti inviati via GET
if (!empty($errore)) {
    $descrizione = $_GET['descrizione'] ?? '';
    $completato = $_GET['completato'] ?? 0;
    $id_categoria = $_GET['id_categoria'] ?? null;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Modifica TODO</title>
</head>
<body>
<h1>Modifica TODO #<?= $id ?></h1>

<?php if ($errore): ?>
    <p style="color:red;"><?= htmlspecialchars($errore) ?></p>
<?php endif; ?>

<form action="eseguiModificaTodo.php" method="post">
    <input type="hidden" name="id" value="<?= $id ?>">

    <label>Descrizione:
        <input type="text" name="descrizione" value="<?= htmlspecialchars($descrizione) ?>">
    </label><br><br>

    <label>Completato:
        <input type="checkbox" name="completato" value="1" <?= $completato ? 'checked' : '' ?>>
    </label><br><br>

    <label>Categoria:
        <select name="id_categoria">
            <option value="">-- Nessuna --</option>
            <?php while ($cat = $cat_result->fetch_assoc()) { ?>
                <option value="<?= $cat['id_c'] ?>"
                    <?= ($id_categoria == $cat['id_c']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nome']) ?>
                </option>
            <?php } ?>
        </select>
    </label><br><br>

    <button type="submit">Salva modifiche</button>
</form>

</body>
</html>

<?php
// Connessione al DB
$mysqli = new mysqli("localhost", "root", "", "chatgptdb");
if ($mysqli->connect_errno) {
    die("Connessione fallita: " . $mysqli->connect_error);
}

// Query con LEFT JOIN per includere anche TODO senza categoria
$sql = "SELECT t.id, t.descrizione, t.completato, c.nome AS categoria_nome
        FROM todo t
        LEFT JOIN categoria c ON t.id_categoria = c.id_c";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Elenco TODO</title>
</head>
<body>
<h1>Elenco TODO</h1>

<form action="cancellazioneMultiplaTodo.php" method="post">
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Seleziona</th>
            <th>ID</th>
            <th>Descrizione</th>
            <th>Completato</th>
            <th>Categoria</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><input type="checkbox" name="selezionati[]" value="<?= $row['id'] ?>"></td>
                <td><?= $row['id'] ?></td>
                <td>
                    <a href="modificaTodo.php?id=<?= $row['id'] ?>">
                        <?= htmlspecialchars($row['descrizione']) ?>
                    </a>
                </td>
                <td><?= $row['completato'] ? 'Sì' : 'No' ?></td>
                <td><?= htmlspecialchars($row['categoria_nome'] ?? '-') ?></td>
            </tr>
        <?php } ?>
    </table>
    <br>
    <button type="submit">Cancella selezionati</button>
</form>

</body>
</html>

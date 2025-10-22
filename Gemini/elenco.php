<?php
include 'db_connect.php';

// Query per recuperare i TODO con il nome della categoria (LEFT JOIN)
// Usiamo LEFT JOIN per includere anche i TODO senza categoria (categoria_id IS NULL)
$sql = "SELECT t.id, t.descrizione, t.completato, c.nome AS nome_categoria
        FROM todo t
        LEFT JOIN categoria c ON t.categoria_id = c.id_c
        ORDER BY t.id DESC";

$stmt = $conn->query($sql);
$todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <title>Elenco TODO</title>
</head>
<body>
    <h1>Elenco TODO</h1>

    <?php 
    // Messaggio di successo dopo la modifica/cancellazione
    if (isset($_GET['status']) && $_GET['status'] == 'success') {
        echo '<p style="color: green;">Operazione completata con successo!</p>';
    }
    ?>

    <form action="cancellazioneMultiplaTodo.php" method="POST">
        <table border="1">
            <thead>
                <tr>
                    <th>Sel.</th>
                    <th>ID</th>
                    <th>Descrizione</th>
                    <th>Completato</th>
                    <th>Categoria</th>
                    <th>Azione</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($todos) > 0): ?>
                    <?php foreach ($todos as $todo): ?>
                        <tr>
                            <td><input type="checkbox" name="ids_da_cancellare[]" value="<?php echo htmlspecialchars($todo['id']); ?>"></td>
                            <td><?php echo htmlspecialchars($todo['id']); ?></td>
                            <td><?php echo htmlspecialchars($todo['descrizione']); ?></td>
                            <td><?php echo $todo['completato'] ? '✅ Sì' : '❌ No'; ?></td>
                            <td><?php echo htmlspecialchars($todo['nome_categoria'] ?? 'Nessuna'); ?></td>
                            <td><a href="modificaTodo.php?id=<?php echo htmlspecialchars($todo['id']); ?>">Modifica</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">Nessun TODO presente.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if (count($todos) > 0): ?>
            <button type="submit" onclick="return confirm('Sei sicuro di voler cancellare gli elementi selezionati?');">Cancella Selezionati</button>
        <?php endif; ?>
    </form>
</body>
</html>

<?php
require_once 'config.php';

$conn = getDBConnection();

// Query con LEFT JOIN per includere anche i TODO senza categoria
$sql = "SELECT t.id, t.descrizione, t.completato, t.categoria, c.nome AS nome_categoria 
        FROM todo t 
        LEFT JOIN categoria c ON t.categoria = c.id_c 
        ORDER BY t.id DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elenco TODO</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 30px;
        }
        
        h1 {
            color: #667eea;
            margin-bottom: 30px;
            text-align: center;
            font-size: 2.5em;
        }
        
        .actions {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .btn-danger:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th {
            background-color: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .completato-si {
            color: #27ae60;
            font-weight: bold;
        }
        
        .completato-no {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .categoria-badge {
            background-color: #3498db;
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 14px;
        }
        
        .categoria-vuota {
            color: #999;
            font-style: italic;
        }
        
        .link-modifica {
            color: #667eea;
            text-decoration: none;
            cursor: pointer;
        }
        
        .link-modifica:hover {
            text-decoration: underline;
        }
        
        .checkbox-cell {
            width: 50px;
            text-align: center;
        }
        
        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .messaggio {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .messaggio-successo {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
    <script>
        function verificaSelezionati() {
            const checkboxes = document.querySelectorAll('input[name="todo_ids[]"]:checked');
            const btnElimina = document.getElementById('btnElimina');
            btnElimina.disabled = checkboxes.length === 0;
        }
        
        function confermaEliminazione() {
            const checkboxes = document.querySelectorAll('input[name="todo_ids[]"]:checked');
            const count = checkboxes.length;
            
            if (count === 0) {
                alert('Seleziona almeno un TODO da eliminare');
                return false;
            }
            
            return confirm(`Sei sicuro di voler eliminare ${count} TODO selezionati?`);
        }
        
        function selezionaTutti(checkbox) {
            const checkboxes = document.querySelectorAll('input[name="todo_ids[]"]');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
            verificaSelezionati();
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>📝 Gestione TODO</h1>
        
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="messaggio messaggio-successo">
                ✓ Operazione completata con successo!
            </div>
        <?php endif; ?>
        
        <form method="POST" action="cancellazioneMultiplaTodo.php" onsubmit="return confermaEliminazione()">
            <div class="actions">
                <button type="submit" id="btnElimina" class="btn btn-danger" disabled>
                    🗑️ Elimina Selezionati
                </button>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th class="checkbox-cell">
                            <input type="checkbox" onchange="selezionaTutti(this)">
                        </th>
                        <th>ID</th>
                        <th>Descrizione</th>
                        <th>Stato</th>
                        <th>Categoria</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="checkbox-cell">
                                    <input type="checkbox" name="todo_ids[]" value="<?php echo $row['id']; ?>" 
                                           onchange="verificaSelezionati()">
                                </td>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td>
                                    <a href="modificaTodo.php?id=<?php echo $row['id']; ?>" class="link-modifica">
                                        <?php echo htmlspecialchars($row['descrizione']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($row['completato']): ?>
                                        <span class="completato-si">✓ Completato</span>
                                    <?php else: ?>
                                        <span class="completato-no">○ Da fare</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['nome_categoria']): ?>
                                        <span class="categoria-badge">
                                            <?php echo htmlspecialchars($row['nome_categoria']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="categoria-vuota">Nessuna categoria</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #999; padding: 30px;">
                                Nessun TODO presente
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
    </div>
</body>
</html>
<?php
$conn->close();
?>

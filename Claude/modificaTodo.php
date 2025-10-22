<?php
require_once 'config.php';

$conn = getDBConnection();
$errori = array();
$todo = null;

// Verifica che sia stato passato un ID valido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: elenco.php');
    exit;
}

$id_todo = intval($_GET['id']);

// CASO 2: Se ci sono errori dalla validazione, usa i dati POST
if (isset($_GET['errore']) && $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['desc'])) {
    $todo = array(
        'id' => $id_todo,
        'descrizione' => $_GET['desc'],
        'completato' => isset($_GET['comp']) ? $_GET['comp'] : 0,
        'categoria' => isset($_GET['cat']) ? $_GET['cat'] : null
    );
    $errori[] = urldecode($_GET['errore']);
} 
// CASO 1: Prima volta che accedi alla pagina - preleva dal DB
else {
    $stmt = $conn->prepare("SELECT id, descrizione, completato, categoria FROM todo WHERE id = ?");
    $stmt->bind_param("i", $id_todo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('Location: elenco.php');
        exit;
    }
    
    $todo = $result->fetch_assoc();
    $stmt->close();
}

// Recupera tutte le categorie per la select
$categorie_result = $conn->query("SELECT id_c, nome FROM categoria ORDER BY nome");
$categorie = array();
if ($categorie_result) {
    while ($cat = $categorie_result->fetch_assoc()) {
        $categorie[] = $cat;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica TODO</title>
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
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
        }
        
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            text-align: center;
            font-size: 2.5em;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        
        .errori {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .errori ul {
            margin-left: 20px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 16px;
        }
        
        textarea, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        textarea:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .checkbox-group label {
            margin: 0;
            cursor: pointer;
        }
        
        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background-color: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        
        .info-text {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✏️ Modifica TODO</h1>
        <p class="subtitle">ID: <?php echo htmlspecialchars($todo['id']); ?></p>
        
        <?php if (!empty($errori)): ?>
            <div class="errori">
                <strong>⚠️ Errori di validazione:</strong>
                <ul>
                    <?php foreach ($errori as $errore): ?>
                        <li><?php echo htmlspecialchars($errore); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="eseguiModificaTodo.php">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($todo['id']); ?>">
            
            <div class="form-group">
                <label for="descrizione">Descrizione *</label>
                <textarea name="descrizione" id="descrizione" required><?php echo htmlspecialchars($todo['descrizione']); ?></textarea>
                <p class="info-text">Minimo 2 caratteri</p>
            </div>
            
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" name="completato" id="completato" value="1" 
                           <?php echo $todo['completato'] ? 'checked' : ''; ?>>
                    <label for="completato">Completato</label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="categoria">Categoria</label>
                <select name="categoria" id="categoria">
                    <option value="">-- Nessuna categoria --</option>
                    <?php foreach ($categorie as $cat): ?>
                        <option value="<?php echo $cat['id_c']; ?>" 
                                <?php echo ($todo['categoria'] == $cat['id_c']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="buttons">
                <button type="submit" class="btn btn-primary">💾 Salva Modifiche</button>
                <a href="elenco.php" class="btn btn-secondary">← Annulla</a>
            </div>
        </form>
    </div>
</body>
</html>
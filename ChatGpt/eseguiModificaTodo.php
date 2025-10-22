<?php
$mysqli = new mysqli("localhost", "root", "", "chatgptdb");
if ($mysqli->connect_errno) {
    die("Connessione fallita: " . $mysqli->connect_error);
}

$id = intval($_POST['id']);
$descrizione = trim($_POST['descrizione']);
$completato = isset($_POST['completato']) ? 1 : 0;
$id_categoria = $_POST['id_categoria'] !== '' ? intval($_POST['id_categoria']) : 'NULL';

// Controllo lunghezza descrizione
if (strlen($descrizione) < 2) {
    $errore = "La descrizione deve essere lunga almeno 2 caratteri";
    header("Location: modificaTodo.php?id=$id&errore=" . urlencode($errore) .
        "&descrizione=" . urlencode($descrizione) .
        "&completato=$completato" .
        "&id_categoria=" . urlencode($_POST['id_categoria']));
    exit;
}

// Costruzione query con categoria opzionale
$sql = "UPDATE todo SET 
        descrizione = '" . $mysqli->real_escape_string($descrizione) . "',
        completato = $completato,
        id_categoria = $id_categoria
        WHERE id = $id";

$mysqli->query($sql);

header("Location: elenco.php");
exit;

<?php
$mysqli = new mysqli("localhost", "root", "", "chatgptdb");
if ($mysqli->connect_errno) {
    die("Connessione fallita: " . $mysqli->connect_error);
}

if (isset($_POST['selezionati']) && is_array($_POST['selezionati'])) {
    $ids = array_map('intval', $_POST['selezionati']);
    if (count($ids) > 0) {
        $ids_list = implode(',', $ids);
        $mysqli->query("DELETE FROM todo WHERE id IN ($ids_list)");
    }
}

header("Location: elenco.php");
exit;

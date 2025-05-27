<?php
require_once 'db.php';

$char_id = $_GET['id'] ?? 0;

$stmt = $con->prepare("SELECT character_name, level, alignment, character_class, background, species, subspecies FROM characters WHERE character_id = ?");
$stmt->bind_param("i", $char_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Character not found']);
}
?>
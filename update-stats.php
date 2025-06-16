<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Not logged in");
}

$character_id = (int)$_GET['id'];
$stmt = $con->prepare("SELECT * FROM characters WHERE character_id = ?");
$stmt->bind_param("i", $character_id);
$stmt->execute();
$result = $stmt->get_result();
$char = $result->fetch_assoc();
$stmt->close();

$editable_stats = [
    'Farmer' => ['base_strength', 'base_constitution', 'base_wisdom'],
    'Charlatan' => ['base_charisma', 'base_dexterity', 'base_intelligence'],
];

$background = $char['background'];
$allowed_stats = $editable_stats[$background] ?? [];

$total_increase = 0;
$updates = [];
$params = [];
$types = '';

foreach ($allowed_stats as $stat) {
    if (isset($_POST[$stat])) {
        $new_val = (int)$_POST[$stat];
        $base_val = (int)$char[$stat];

        if ($new_val < $base_val || $new_val > $base_val + 3) {
            continue; // sigurnosna provjera
        }

        $increase = $new_val - $base_val;
        $total_increase += $increase;

        if ($increase > 0) {
            $updates[] = "$stat = ?";
            $params[] = $new_val;
            $types .= 'i';
        }
    }
}

if ($total_increase > 3) {
    die("You can only increase up to 3 total stat points.");
}

if (!empty($updates)) {
    $sql = "UPDATE characters SET " . implode(', ', $updates) . " WHERE character_id = ?";
    $params[] = $character_id;
    $types .= 'i';

    $stmt = $con->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();
}

header("Location: character.php?id=" . $character_id);
exit;

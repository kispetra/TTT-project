<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Not logged in");
}

$armor = $_POST['armor'] ?? 'None';
$weapons = $_POST['weapons'] ?? null;

$character_id = isset($_POST['character_id']) ? (int)$_POST['character_id'] : 0;
$stmt = $con->prepare("SELECT * FROM characters WHERE character_id = ?");
$stmt->bind_param("i", $character_id);
$stmt->execute();
$result = $stmt->get_result();
$char = $result->fetch_assoc();
$stmt->close();

$editable_stats = [
    'Farmer' => ['base_strength', 'base_constitution', 'base_wisdom'],
    'Charlatan' => ['base_charisma', 'base_dexterity', 'base_intelligence'],
    'Acolyte'     => ['base_wisdom', 'base_charisma', 'base_intelligence'],
    'Artisan'     => ['base_intelligence', 'base_wisdom', 'base_constitution'],
    'Criminal'    => ['base_dexterity', 'base_charisma', 'base_intelligence'],
    'Entertainer' => ['base_charisma', 'base_dexterity', 'base_strength'],
    'Guard'       => ['base_strength', 'base_dexterity', 'base_constitution'],
    'Guide'       => ['base_wisdom', 'base_dexterity', 'base_intelligence'],
    'Hermit'      => ['base_wisdom', 'base_intelligence', 'base_constitution'],
    'Merchant'    => ['base_charisma', 'base_intelligence', 'base_wisdom'],
    'Noble'       => ['base_charisma', 'base_intelligence', 'base_wisdom'],
    'Sage'        => ['base_intelligence', 'base_wisdom', 'base_charisma'],
    'Sailor'      => ['base_strength', 'base_dexterity', 'base_constitution'],
    'Scribe'      => ['base_intelligence', 'base_wisdom', 'base_charisma'],
    'Soldier'     => ['base_strength', 'base_constitution', 'base_dexterity'],
    'Wayfarer'    => ['base_wisdom', 'base_dexterity', 'base_constitution'],
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
            continue; 
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

// Armor update
if ($armor !== $char['armor']) {
    $updates[] = "armor = ?";
    $params[] = $armor;
    $types .= 's';
}

// Weapon update
if ($weapons !== null && $weapons !== $char['weapons']) {
    $updates[] = "weapons = ?";
    $params[] = $weapons;
    $types .= 's';
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

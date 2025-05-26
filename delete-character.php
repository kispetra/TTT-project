<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? 0;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: profile.php");
    exit;
}

$character_id = intval($_GET['id']);

$stmt = $con->prepare("DELETE FROM characters WHERE character_id = ? AND user_id = ?");
$stmt->bind_param("ii", $character_id, $user_id);
$stmt->execute();

$stmt->close();

header("Location: profile.php");
exit;

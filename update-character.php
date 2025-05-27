<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $level = $_POST['level'] ?? null;
    $alignment = trim($_POST['alignment'] ?? '');
    $character_class = trim($_POST['character_class'] ?? '');
    $background = trim($_POST['background'] ?? '');
    $species = trim($_POST['species'] ?? '');
    $subspecies = $_POST['subspecies'] ?? null;

    if (!$id || !$name || !$level || !$alignment || !$character_class || !$background || !$species) {
        $_SESSION['error'] = "Svi podaci su obavezni!";
        header("Location: profile.php");
        exit;
    }

    $stmt = $con->prepare("SELECT user_id FROM characters WHERE character_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($owner_id);
    if (!$stmt->fetch() || $owner_id != $user_id) {
        $stmt->close();
        $_SESSION['error'] = "Nemate pravo uređivati ovog lika!";
        header("Location: profile.php");
        exit;
    }
    $stmt->close();

    $stmt = $con->prepare("UPDATE characters SET character_name = ?, level = ?, alignment = ?, character_class = ?, background = ?, species = ?, subspecies = ?  WHERE character_id = ?");
    $stmt->bind_param("sisssssi", $name, $level, $alignment, $character_class, $background, $species, $subspecies, $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Lik je uspješno ažuriran.";
    } else {
        $_SESSION['error'] = "Došlo je do greške prilikom ažuriranja.";
    }

    $stmt->close();
    header("Location: profile.php");
    exit;
} else {
    header("Location: profile.php");
    exit;
}
?>
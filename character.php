<?php
require_once 'db.php';
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid character ID');
}

$character_id = (int)$_GET['id'];
$stmt = $con->prepare("SELECT * FROM characters WHERE character_id = ?");
$stmt->bind_param("i", $character_id);
$stmt->execute();
$result = $stmt->get_result();
$char = $result->fetch_assoc();
$stmt->close();

if (!$char) {
    die('Character not found');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($char['character_name']) ?> - Info</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container">
        <h1><?= htmlspecialchars($char['character_name']) ?></h1>
        <p><strong>Level:</strong> <?= htmlspecialchars($char['level']) ?></p>
        <p><strong>Alignment:</strong> <?= htmlspecialchars($char['alignment']) ?></p>
        <p><strong>Class:</strong> <?= htmlspecialchars($char['character_class']) ?></p>
        <p><strong>Background:</strong> <?= htmlspecialchars($char['background']) ?></p>
        <p><strong>Species:</strong> <?= htmlspecialchars($char['species']) ?></p>
        <?php if (!empty($char['subspecies'])): ?>
            <p><strong>Subspecies:</strong> <?= htmlspecialchars($char['subspecies']) ?></p>
        <?php endif; ?>
        <a href="profile.php" class="btn btn-secondary">Back to Profile</a>
    </div>
</body>
</html>

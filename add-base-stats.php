<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
if (empty($user_id)) {
    $error_msg = "User session error. Please log in again.";
header('Location: login.php');
exit;
}

$success_msg = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnCreate'])) {

    $base_strength = $_POST['base-strength'] ?? 0;
    $base_dexterity = $_POST['base-dexterity'] ?? 0;
    $base_constitution = $_POST['base-constitution'] ?? 0;
    $base_intelligence = $_POST['base-intelligence'] ?? 0;
    $base_wisdom = $_POST['base-wisdom'] ?? 0;
    $base_charisma = $_POST['base-charisma'] ?? 0;

    $character_id = $_SESSION['created_character_id'] ?? null;

    if ($character_id) {
        $sql = "UPDATE characters SET base_strength = ?, base_dexterity = ?, base_constitution = ?, base_intelligence = ?, base_wisdom = ?, base_charisma = ? WHERE character_id = ?";
        $stmt = $con->prepare($sql);
        if ($stmt) {
            $stmt->bind_param(
                "iiiiiii",
                $base_strength,
                $base_dexterity,
                $base_constitution,
                $base_intelligence,
                $base_wisdom,
                $base_charisma,
                $character_id
            );

            if ($stmt->execute()) {
                $success_msg = "Character stats successfully added!";
                header("Location: profile.php");
                exit;
            } else {
                $error_msg = "Error updating character: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $error_msg = "Prepare failed: " . $con->error;
        }
    } else {
        $error_msg = "Character ID not found in session. Please create a character first.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Base Stats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./add-character.css">
    <link href="https://fonts.googleapis.com/css2?family=Cardo&family=Cinzel:wght@600&display=swap" rel="stylesheet">
</head>

<body>
    <div class="modal-wrapper">
        <div class="container character-form-container">
            <h2 class="modal-header">Add Base Stats</h2>
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
            <?php elseif (!empty($error_msg)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>

            <form method="post" action="add-base-stats.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="base-strength" class="form-label">Base Strength</label>
                        <input type="number" class="form-control" id="base-strength" name="base-strength"
                            value="<?= htmlspecialchars($_POST['base-strength'] ?? '') ?>"
                            min="0" max="20" step="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="base-dexterity" class="form-label">Base Dexterity</label>
                        <input type="number" class="form-control" id="base-dexterity" name="base-dexterity"
                            value="<?= htmlspecialchars($_POST['base-dexterity'] ?? '') ?>"
                            min="0" max="20" step="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="base-constitution" class="form-label">Base Constitution</label>
                        <input type="number" class="form-control" id="base-constitution" name="base-constitution"
                            value="<?= htmlspecialchars($_POST['base-constitution'] ?? '') ?>"
                            min="0" max="20" step="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="base-intelligence" class="form-label">Base Intelligence</label>
                        <input type="number" class="form-control" id="base-intelligence" name="base-intelligence"
                            value="<?= htmlspecialchars($_POST['base-intelligence'] ?? '') ?>"
                            min="0" max="20" step="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="base-wisdom" class="form-label">Base Wisdom</label>
                        <input type="number" class="form-control" id="base-wisdom" name="base-wisdom"
                            value="<?= htmlspecialchars($_POST['base-wisdom'] ?? '') ?>"
                            min="0" max="20" step="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="base-charisma" class="form-label">Base Charisma</label>
                        <input type="number" class="form-control" id="base-charisma" name="base-charisma"
                            value="<?= htmlspecialchars($_POST['base-charisma'] ?? '') ?>"
                            min="0" max="20" step="1" required>
                    </div>
                </div>
                <div class="buttons">
                    <button type="button" class="btn btn-back" onclick="window.history.back();">Back</button>
                    <button type="submit" name="btnCreate" class="btn btn-next">Create Character</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
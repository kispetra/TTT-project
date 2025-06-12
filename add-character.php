<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? '';
$success_msg = "";
$error_msg = "";


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['next'])) {
    $_SESSION['character_name'] = $_POST['character_name'];
    $_SESSION['level'] = $_POST['level'];
    $_SESSION['alignment'] = $_POST['alignment'];

    $_SESSION['user_id'] = $user_id;

    session_write_close();
    header('Location: addCharacterClass.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Character</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./addCharacter.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./addCharacter.css">
    <link href="https://fonts.googleapis.com/css2?family=Cardo&family=Cinzel:wght@600&display=swap" rel="stylesheet">
</head>

<body>
    <div class="modal-wrapper">
        <div class="container character-form-container">
            <h2 class="modal-header">Create Your Character</h2>
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
            <?php elseif (!empty($error_msg)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>

            <form method="post" action="addCharacter.php">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="character_name" class="form-label">Character Name</label>
                        <input type="text" class="form-control" id="character_name" name="character_name" value="<?= htmlspecialchars($_SESSION['character_name'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="level" class="form-label">Level</label>
                        <input type="number" class="form-control" id="level" name="level"
                            value="<?= htmlspecialchars($_SESSION['level'] ?? '') ?>"
                            min="1" max="20" step="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="alignment" class="form-label">Alignment</label>
                        <select class="form-select" id="alignment" name="alignment" required>
                            <option value="" disabled <?= !isset($_SESSION['alignment']) || $_SESSION['alignment'] === '' ? 'selected' : '' ?>>Choose alignment</option>
                            <option value="Lawful Good" <?= ($_SESSION['alignment'] ?? '') === 'Lawful Good' ? 'selected' : '' ?>>Lawful Good</option>
                            <option value="Neutral Good" <?= ($_SESSION['alignment'] ?? '') === 'Neutral Good' ? 'selected' : '' ?>>Neutral Good</option>
                            <option value="Chaotic Good" <?= ($_SESSION['alignment'] ?? '') === 'Chaotic Good' ? 'selected' : '' ?>>Chaotic Good</option>
                            <option value="Lawful Neutral" <?= ($_SESSION['alignment'] ?? '') === 'Lawful Neutral' ? 'selected' : '' ?>>Lawful Neutral</option>
                            <option value="True Neutral" <?= ($_SESSION['alignment'] ?? '') === 'True Neutral' ? 'selected' : '' ?>>True Neutral</option>
                            <option value="Chaotic Neutral" <?= ($_SESSION['alignment'] ?? '') === 'Chaotic Neutral' ? 'selected' : '' ?>>Chaotic Neutral</option>
                            <option value="Lawful Evil" <?= ($_SESSION['alignment'] ?? '') === 'Lawful Evil' ? 'selected' : '' ?>>Lawful Evil</option>
                            <option value="Neutral Evil" <?= ($_SESSION['alignment'] ?? '') === 'Neutral Evil' ? 'selected' : '' ?>>Neutral Evil</option>
                            <option value="Chaotic Evil" <?= ($_SESSION['alignment'] ?? '') === 'Chaotic Evil' ? 'selected' : '' ?>>Chaotic Evil</option>
                            <?php unset($_SESSION['character_name'], $_SESSION['level'], $_SESSION['alignment']); ?>
                        </select>
                    </div>
                    <div class="buttons">
                        <button type="button" class="btn btn-back" onclick="window.history.back();">Back</button>
                        <button type="submit" name="next" class="btn btn-next">Next</button>
                    </div>
                </div>
            </form>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.alignment-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('alignment').value = button.getAttribute('data-value');
                const modal = bootstrap.Modal.getInstance(document.getElementById('alignmentModal'));
                modal.hide();
            });
        });
    </script>

</body>

</html>
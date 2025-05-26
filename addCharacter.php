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
</head>
<body>

<div class="container mt-5">
    <h2>Create Your Character</h2>
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
    <?php elseif (!empty($error_msg)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>
    <form method="post" action="addCharacter.php"> 

        <div class="mb-3">
            <label for="character_name" class="form-label">Character Name</label>
            <input type="text" class="form-control" id="character_name" name="character_name" required>
        </div>

        <div class="mb-3">
            <label for="level" class="form-label">Level</label>
            <input type="number" class="form-control" id="level" name="level" min="1" max="20" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Alignment</label>
            <div class="input-group">
                <input type="text" class="form-control" id="alignment" name="alignment" required>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#alignmentModal">Choose</button>
            </div>
        </div>
        <button type="submit" name="next" class="btn btn-primary">Next</button>
    </form>
</div>

<div class="modal fade" id="alignmentModal" tabindex="-1" aria-labelledby="alignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Alignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php
                $alignments = [
                    "Lawful Good", "Neutral Good", "Chaotic Good",
                    "Lawful Neutral", "True Neutral", "Chaotic Neutral",
                    "Lawful Evil", "Neutral Evil", "Chaotic Evil"
                ];
                foreach ($alignments as $alignment) {
                    echo "<button type='button' class='btn btn-outline-dark w-100 mb-2 alignment-btn' data-value='$alignment'>$alignment</button>";
                }
                ?>
            </div>
        </div>
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

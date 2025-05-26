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
    $_SESSION['character_class']= $_POST['character_class'];
    
    $_SESSION['user_id'] = $user_id;

    session_write_close();
    header('Location: createCharacter.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Class</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Choose Class of Your Character</h2>
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
    <?php elseif (!empty($error_msg)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>
    <form method="post" action="addCharacterClass.php"> 

        <div class="mb-3">
            <label class="form-label">Class</label>
            <div class="input-group">
                <input type="text" class="form-control" id="character_class" name="character_class" required>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#classModal">Choose</button>
            </div>
        </div>

        <button type="submit" name="next" class="btn btn-primary">Next</button>
    </form>
</div>
   

<div class="modal fade" id="classModal" tabindex="-1" aria-labelledby="classModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php
                $classes = [
                    "Barbarian", "Bard", "Cleric", "Druid", "Fighter",
                    "Monk", "Paladin", "Ranger", "Rogue", "Sorcerer",
                    "Warlock", "Wizard"
                ];
                foreach ($classes as $character_class) {
                    echo "<button type='button' class='btn btn-outline-primary w-100 mb-2 class-btn' data-value='$character_class'>$character_class</button>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.class-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('character_class').value = button.getAttribute('data-value');
            const modal = bootstrap.Modal.getInstance(document.getElementById('classModal'));
            modal.hide();
        });
    });
</script>

</body>
</html>

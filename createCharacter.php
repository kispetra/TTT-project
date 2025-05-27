<?php
session_start();

require_once 'db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? '';
$first_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'First';
$last_name = isset($_SESSION['last_name']) ? $_SESSION['last_name'] : 'Last';

$success_msg = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['createCharacter'])) {
    $character_name = $_SESSION['character_name'] ?? '';
    $level = $_SESSION['level'] ?? '';
    $alignment = $_SESSION['alignment'] ?? '';
    $character_class = $_SESSION['character_class'] ?? '';

    $_SESSION['user_id'] = $user_id;

    $background = $_POST['background'];
    $species = $_POST['species'];
    $subspecies      = $_POST['subspecies'] ?? null;


    if (
        !$character_name || !$level || !$alignment || !$character_class
        || !$background || !$species
        || (isset($subspeciesMap[$species]) && !$subspecies)
    ) {
        $error_msg = "All character data is required.";
    } else {
        $query = "INSERT INTO characters (character_name, level, alignment, character_class, background, species, subspecies, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($query);
        $stmt->bind_param(
            "sisssssi",
            $character_name,
            $level,
            $alignment,
            $character_class,
            $background,
            $species,
            $subspecies,
            $user_id
        );

        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Character successfully created!";

            unset($_SESSION['character_name'], $_SESSION['level'], $_SESSION['alignment'], $_SESSION['character_class']);
            header('Location: profile.php');
            exit;
        } else {
            $error_msg = "Database error: " . $stmt->error;
        }

        $stmt->close();
    }
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
        <form method="post" action="createCharacter.php">
            <input type="hidden" name="createCharacter" value="1">


            <div class="mb-3">
                <label class="form-label">Background</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="background" name="background" required>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#backgroundModal">Choose</button>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Species</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="species" name="species" required>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#speciesModal">Choose</button>
                </div>
            </div>

            <div class="mb-3" id="subspecies-group" style="display:none;">
                <label class="form-label">Subspecies</label>
                <select class="form-select" name="subspecies" id="subspecies-select" required>
                    <option value="">— select —</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Create Character</button>
        </form>
    </div>

    <div class="modal fade" id="backgroundModal" tabindex="-1" aria-labelledby="backgroundModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Background</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    $backgrounds = [
                        "Acolyte",
                        "Artisan",
                        "Charlatan",
                        "Criminal",
                        "Entertainer",
                        "Farmer",
                        "Guard",
                        "Guide",
                        "Hermit",
                        "Merchant",
                        "Noble",
                        "Sage",
                        "Sailor",
                        "Scribe",
                        "Soldier",
                        "Wayfarer"
                    ];
                    foreach ($backgrounds as $background) {
                        echo "<button type='button' class='btn btn-outline-dark w-100 mb-2 background-btn' data-value='$background'>$background</button>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="speciesModal" tabindex="-1" aria-labelledby="speciesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Species</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    $all_species = [
                        "Aasimar",
                        "Dragoborn",
                        "Dwarf",
                        "Elf",
                        "Gnome",
                        "Goliath",
                        "Halfling",
                        "Human",
                        "Orc",
                        "Tiefling"
                    ];
                    foreach ($all_species as $species) {
                        echo "<button type='button' class='btn btn-outline-primary w-100 mb-2 species-btn' data-value='$species'>$species</button>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap + JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.background-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('background').value = button.getAttribute('data-value');
                const modal = bootstrap.Modal.getInstance(document.getElementById('backgroundModal'));
                modal.hide();
            });
        });

        document.querySelectorAll('.species-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('species').value = button.getAttribute('data-value');
                const modal = bootstrap.Modal.getInstance(document.getElementById('speciesModal'));
                modal.hide();
            });
        });
    </script>

    <script>
        const subspeciesMap = {
            "Dragonborn": ["Black", "Blue", "Brass", "Bronze", "Copper"],
            "Elf": ["High", "Wood", "Drow"],
            "Gnome": ["Forest", "Rock"],
            "Goliath": ["Cloud", "Fire", "Frost", "Hill", "Stone", "Storm"], 
            "Tiefling": ["Abyssal", "Chthonic", "Internal"]
        };

        const speciesInput = document.getElementById('species');
        const subsGroup = document.getElementById('subspecies-group');
        const subsSelect = document.getElementById('subspecies-select');

        function updateSubspecies() {
            const chosen = speciesInput.value;
            const list = subspeciesMap[chosen] || null;

            if (list) {
                subsSelect.innerHTML = '<option value="">— select —</option>' +
                    list.map(s => `<option value="${s}">${s}</option>`).join('');
                subsGroup.style.display = '';
                subsSelect.required = true;
            } else {
                subsGroup.style.display = 'none';
                subsSelect.required = false;
                subsSelect.value = '';
            }
        }

        speciesInput.addEventListener('change', updateSubspecies);
        document.querySelectorAll('.species-btn').forEach(btn =>
            btn.addEventListener('click', () => {
                setTimeout(updateSubspecies, 0);
            })
        );
    </script>

</body>

</html>
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
            $_SESSION['created_character_id'] = $stmt->insert_id; 
            unset($_SESSION['character_name'], $_SESSION['level'], $_SESSION['alignment'], $_SESSION['character_class']);
            header('Location: add-base-stats.php');
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
    <link rel="stylesheet" href="./create-character.css">
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
            <form method="post" action="create-character.php">
                <input type="hidden" name="createCharacter" value="1">


                <div class="mb-3">
                    <label class="form-label">Background</label>
                    <select class="form-select" id="background" name="background" required>
                        <option value="" disabled selected>Choose background</option>
                        <option value="Acolyte">Acolyte</option>
                        <option value="Artisan">Artisan</option>
                        <option value="Charlatan">Charlatan</option>
                        <option value="Criminal">Criminal</option>
                        <option value="Entertainer">Entertainer</option>
                        <option value="Farmer">Farmer</option>
                        <option value="Guard">Guard</option>
                        <option value="Guide">Guide</option>
                        <option value="Hermit">Hermit</option>
                        <option value="Merchant">Merchant</option>
                        <option value="Noble">Noble</option>
                        <option value="Sage">Sage</option>
                        <option value="Sailor">Sailor</option>
                        <option value="Scribe">Scribe</option>
                        <option value="Soldier">Soldier</option>
                        <option value="Wayfarer">Wayfarer</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Species</label>
                    <select class="form-select" id="species" name="species" required>
                        <option value="" disabled selected>Choose species</option>
                        <option value="Aasimar">Aasimar</option>
                        <option value="Dragonborn">Dragonborn</option>
                        <option value="Dwarf">Dwarf</option>
                        <option value="Elf">Elf</option>
                        <option value="Gnome">Gnome</option>
                        <option value="Goliath">Goliath</option>
                        <option value="Halfling">Halfling</option>
                        <option value="Human">Human</option>
                        <option value="Orc">Orc</option>
                        <option value="Tiefling">Tiefling</option>
                    </select>
                    
                </div>

                <div class="mb-3" id="subspecies-group" style="display:none;">
                    <label class="form-label">Subspecies</label>
                    <select class="form-select" name="subspecies" id="subspecies-select" required>
                        <option value="">Choose Subspecies</option>
                    </select>
                </div>

                <div class="buttons">
                    <button type="button" class="btn btn-back" onclick="window.history.back();">Back</button>
                    <button type="submit" name="btnCreate"class="btn btn-next">Create Character</button>
                </div>      
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
                        "Dragonborn",
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
                subsSelect.innerHTML = '<option value=""> Select subspecies </option>' +
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
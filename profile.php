<?php
session_start();

$success_msg = '';
if (isset($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}
require_once 'db.php';
function shortenName($name)
{
    return mb_strlen($name) > 30 ? mb_substr($name, 0, 30) . '...' : $name;
}
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? 0;

$stmt = $con->prepare("SELECT first_name, last_name, username FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $username);
$stmt->fetch();
$stmt->close();

$characters = [];
$stmt = $con->prepare("SELECT character_id, character_name, level, alignment, character_class, background, species, subspecies FROM characters WHERE user_id = ?");
if (!$stmt) {
    die("Prepare failed: " . $con->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $characters[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?= htmlspecialchars($username) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500&family=Cardo&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="profile.css">
    <link rel="stylesheet" href="navbar.css">

</head>

<body class="bg-light">
    <div class="container">
        <h1 class="fw-body">Welcome, <?= htmlspecialchars($first_name) ?>!</h1>
    </div>

    <div style="text-align: center; font-size: 15px; font-style: italic; color: #6d4c41; margin-top: 10px;">
        "In this game, every choice matters and every story is unique."
    </div>

    <div class="container-2 d-flex justify-content-center gap-3">
        <a href="edit-profile.php" class="btn btn-outline-primary">Edit Profile</a>
        <a href="logout.php" class="btn btn-outline-danger">Log Out</a>
    </div>
</div>




    <div class="bg-white p-4 rounded shadow-sm">
        <div class="text-character">Table of your characters</div>
        <?php if (empty($characters)): ?>
            <p class="text-muted">You haven't created any characters yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <div class="table-container-rounded">
                <table class="table table-hover align-middle text-center ">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Level</th>
                            <th>Class</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $index = 1; ?>
                        <?php foreach ($characters as $char): ?>
                            <tr>
                                <td><?= $index++ ?></td>
                                <td title="<?= htmlspecialchars($char['character_name']) ?>">
                                    <?= htmlspecialchars(shortenName($char['character_name'])) ?>
                                </td>
                                <td><?= htmlspecialchars($char['level']) ?></td>
                                <td><?= htmlspecialchars($char['character_class']) ?></td>
                                <td class="action-buttons">
                                    <button
                                        class="edit-btn"
                                        data-name="<?= htmlspecialchars($char['character_name']) ?>"
                                        data-level="<?= htmlspecialchars($char['level']) ?>"
                                        data-alignment="<?= htmlspecialchars($char['alignment']) ?>"
                                        data-class="<?= htmlspecialchars($char['character_class']) ?>"
                                        data-background="<?= htmlspecialchars($char['background']) ?>"
                                        data-species="<?= htmlspecialchars($char['species']) ?>"
                                        data-subspecies="<?= htmlspecialchars($char['subspecies'] ?? '') ?>"
                                        data-id="<?= $char['character_id'] ?? 0 ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal">
                                        Edit
                                    </button>

                                    <button
                                        class="info-btn"
                                        data-id="<?= $char['character_id'] ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#infoModal">
                                        Info
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                
                </table>
                </div>
            </div>
            <!-- Edit Modal -->
            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" action="update-character.php">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel">Edit Character</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="id" id="char-id">
                                <div class="mb-3">
                                    <label for="char-name" class="form-label">Name</label>
                                    <input type="text" class="form-control" name="name" id="char-name" value="<?= htmlspecialchars(shortenName($row['character_name'])) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="char-level" class="form-label">Level</label>
                                    <input type="number" class="form-control" name="level" id="char-level" required min="1" max="20" step="1">
                                </div>
                                <div class="mb-3">
                                    <label for="char-alignment" class="form-label">Alignment</label>
                                    <select class="form-select" name="alignment" id="char-alignment" required>
                                        <option value="">Select alignment: </option>
                                        <option value="Lawful Good">Lawful Good</option>
                                        <option value="Lawful Neutral">Lawful Neutral</option>
                                        <option value="Lawful Evil">Lawful Evil</option>
                                        <option value="Unaligned">Unaligned</option>
                                        <option value="Neutral Good">Neutral Good</option>
                                        <option value="Neutral">Neutral</option>
                                        <option value="Neutral Evil">Neutral Evil</option>
                                        <option value="Chaotic Good">Chaotic Good</option>
                                        <option value="Chaotic Neutral">Chaotic Neutral</option>
                                        <option value="Chaotic Evil">Chaotic Evil</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="char-character_class" class="form-label">Class</label>
                                    <select class="form-select" name="character_class" id="char-character_class" required>
                                        <option value="">Select class: </option>
                                        <option value="Barbarian">Barbarian</option>
                                        <option value="Bard">Bard</option>
                                        <option value="Cleric">Cleric</option>
                                        <option value="Druid">Druid</option>
                                        <option value="Fighter">Fighter</option>
                                        <option value="Monk">Monk</option>
                                        <option value="Paladin">Paladin</option>
                                        <option value="Ranger">Ranger</option>
                                        <option value="Rogue">Rogue</option>
                                        <option value="Sorcerer">Sorcerer</option>
                                        <option value="Warlock">Warlock</option>
                                        <option value="Wizard">Wizard</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="char-background" class="form-label">Background</label>
                                    <select class="form-select" name="background" id="char-background" required>
                                        <option value="">Select background: </option>
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
                                    <label for="char-species" class="form-label">Species</label>
                                    <select class="form-select" name="species" id="char-species" required>
                                        <option value="">Select species: </option>
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
                                <div class="mb-3">
                                    <label for="char-subspecies" class="form-label">Subspecies</label>
                                    <div id="subspecies-container">
                                        <!-- JS će ovdje dinamički ubaciti dropdown ili tekst -->
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Save</button>
                                <a href="#" id="delete-link" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this character?');">Delete</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Info Modal -->
            <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Character Info</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="info-content">
                            <p>Loading...</p>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>
    <nav class="navbar">
        <a href="home-page.php" class="nav-icon">
            <img src="./img/home.png" alt="Home" />
        </a>
        <a href="addCharacter.php" class="nav-add">
            <img src="./img/add.png" alt="Dodaj" />
        </a>
        <a href="profile.php" class="nav-icon">
            <img src="./img/user.png" alt="Profil" />
        </a>
    </nav>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('char-id').value = button.dataset.id;
                document.getElementById('char-name').value = button.dataset.name;
                document.getElementById('char-level').value = button.dataset.level;
                document.getElementById('char-alignment').value = button.dataset.alignment;
                document.getElementById('char-character_class').value = button.dataset.class;
                document.getElementById('char-background').value = button.dataset.background;
                document.getElementById('char-species').value = button.dataset.species || '';
                toggleSubspeciesField(button.dataset.species);
                document.getElementById('char-subspecies').value = button.dataset.subspecies || 'No subspecies';
                document.getElementById('delete-link').href = `delete-character.php?id=${button.dataset.id}`;
            });
        });

        document.querySelectorAll('.info-btn').forEach(button => {
            button.addEventListener('click', () => {
                const charId = button.dataset.id;

                fetch(`get-character-info.php?id=${charId}`)
                    .then(response => response.json())
                    .then(data => {
                        const content = `
                            <p><strong>Name:&nbsp</strong> ${data.character_name}</p>
                            <p><strong>Level:&nbsp</strong> ${data.level}</p>
                            <p><strong>Alignment:&nbsp</strong> ${data.alignment}</p>
                            <p><strong>Class:&nbsp</strong> ${data.character_class}</p>
                            <p><strong>Background:&nbsp</strong> ${data.background}</p>
                            <p><strong>Species:&nbsp</strong> ${data.species}</p>
                            ${data.subspecies ? `<p><strong>Subspecies:&nbsp</strong> ${data.subspecies}</p>` : ''}
                        `;
                        document.getElementById('info-content').innerHTML = content;
                    })
                    .catch(err => {
                        document.getElementById('info-content').innerHTML = "<p>Error loading data.</p>";
                    });
            });
        });
    </script>
    <script>
        function toggleSubspeciesField(species) {
            const container = document.getElementById('subspecies-container');
            container.innerHTML = '';

            const hasSubspecies = checkIfSpeciesHasSubspecies(species);

            if (hasSubspecies) {
                const select = document.createElement('select');
                select.className = 'form-select';
                select.name = 'subspecies';
                select.id = 'char-subspecies';

                const options = getSubspeciesOptions(species);
                options.forEach(sub => {
                    const opt = document.createElement('option');
                    opt.value = sub;
                    opt.textContent = sub;
                    select.appendChild(opt);
                });

                container.appendChild(select);
            } else {
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control';
                input.name = 'subspecies';
                input.id = 'char-subspecies';
                input.value = 'No subspecies';
                input.readOnly = true;

                container.appendChild(input);
            }
        }

        function checkIfSpeciesHasSubspecies(species) {
            const speciesWithSub = ['Dragonborn', 'Elf', 'Gnome', 'Goliath', 'Tiefling'];
            return speciesWithSub.includes(species);
        }

        function getSubspeciesOptions(species) {
            const subspeciesMap = {
                Dragonborn: ["Black", "Blue", "Brass", "Bronze", "Copper"],
                Elf: ["High", "Wood", "Drow"],
                Gnome: ["Forest", "Rock"],
                Goliath: ["Cloud", "Fire", "Frost", "Hill", "Stone", "Storm"],
                Tiefling: ["Abyssal", "Chthonic", "Internal"]
            };
            return subspeciesMap[species] || [];
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('char-species').addEventListener('change', function() {
                toggleSubspeciesField(this.value);
            });
        });

        function logout() {
            localStorage.removeItem("token")
            window.location.href = "./login.php"
        }
    </script>
</body>

</html>
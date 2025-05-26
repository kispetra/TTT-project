<?php
session_start();

$success_msg = '';
if (isset($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}
require_once 'db.php';

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
$stmt = $con->prepare("SELECT character_id, character_name, level, alignment, character_class, background, species FROM characters WHERE user_id = ?");
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
    <link rel="stylesheet" href="profile.css">
</head>


<body>
    <div class="container">
        <p class="container-name">Welcome, <?= htmlspecialchars($first_name) ?>!</p>
    </div>
    <p>Username:  <strong><?= htmlspecialchars($username) ?></strong></p>
    <div class="container-table">
        <h4>Your Characters</h4>
        <?php if (empty($characters)): ?>
            <p>You haven't created any characters yet.</p>
        <?php else: ?>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Level</th>
                        <th>Alignment</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($characters as $char): ?>
                        <tr>
                            <td><?= htmlspecialchars($char['character_name']) ?></td>
                            <td><?= htmlspecialchars($char['level']) ?></td>
                            <td><?= htmlspecialchars($char['alignment']) ?></td>
                            <td>
                                <button
                                    class="btn btn-sm btn-custom edit-btn"
                                    data-name="<?= htmlspecialchars($char['character_name']) ?>"
                                    data-level="<?= htmlspecialchars($char['level']) ?>"
                                    data-alignment="<?= htmlspecialchars($char['alignment']) ?>"
                                    data-id="<?= $char['character_id'] ?? 0 ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal">
                                    Edit
                                </button>
                                <button
                                    class="btn btn-sm btn-custom info-btn"
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
                                    <input type="text" class="form-control" name="name" id="char-name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="char-level" class="form-label">Level</label>
                                    <input type="number" class="form-control" name="level" id="char-level" required min="1" max="20" step="1">
                                </div>
                                <div class="mb-3">
                                    <label for="char-alignment" class="form-label">Alignment</label>
                                    <select class="form-select" name="alignment" id="char-alignment" required>
                                        <option value="">Select alignment: </option>
                                        <option value="Lawful good">Lawful good</option>
                                        <option value="Lawful neutral">Lawful neutral</option>
                                        <option value="Lawful bad">Lawful bad</option>
                                        <option value="Unaligned">Unaligned</option>
                                        <option value="Neutral good">Neutral good</option>
                                        <option value="Neutral">Neutral</option>
                                        <option value="Neutral evil">Neutral evil</option>
                                        <option value="Chaotic good">Chaotic good</option>
                                        <option value="Chaotic neutral">Chaotic neutral</option>
                                        <option value="Chaotic evil">Chaotic evil</option>
                                    </select>
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
    <div class="navbar">
        <a href="home-page.php" class="nav-icon">
            <img src="./img/home.png" alt="Home" />
        </a>
        <a href="add-character.php" class="nav-add">
            <img src="./img/add.png" alt="Dodaj" />
        </a>
        <a href="profile.php" class="nav-icon">
            <img src="./img/user.png" alt="Profil" />
        </a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('char-id').value = button.dataset.id;
                document.getElementById('char-name').value = button.dataset.name;
                document.getElementById('char-level').value = button.dataset.level;
                document.getElementById('char-alignment').value = button.dataset.alignment;
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
                        <p><strong>Name:&nbsp;</strong> ${data.character_name}</p>
                        <p><strong>Level:&nbsp;</strong> ${data.level}</p>
                        <p><strong>Alignment:&nbsp;</strong> ${data.alignment}</p>
                        <p><strong>Class:&nbsp;</strong> ${data.character_class}</p>
                    `;
                        document.getElementById('info-content').innerHTML = content;
                    })
                    .catch(err => {
                        document.getElementById('info-content').innerHTML = "<p>Error loading data.</p>";
                    });
            });
        });
    </script>
</body>


</html>

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
$stmt = $con->prepare("SELECT character_name, level, alignment, character_class, background, species FROM characters WHERE user_id = ?");
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
    <title>Profile - <?= htmlspecialchars($username) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
    #toastContainer {
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 1100;
    }
</style>

</head>
<body>
    <?php if (!empty($success_msg)): ?>
<div id="toastContainer">
    <div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" id="successToast">
        <div class="d-flex">
            <div class="toast-body">
                <?= htmlspecialchars($success_msg) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container mt-5">
    <h2>Welcome, <?= htmlspecialchars($first_name) . ' ' . htmlspecialchars($last_name) ?>!</h2>
    <p>Username: <strong><?= htmlspecialchars($username) ?></strong></p>

    <hr>

    <h4>Your Characters</h4>
    <?php if (empty($characters)): ?>
        <p>You haven't created any characters yet.</p>
    <?php else: ?>
        <table class="table table-bordered mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Level</th>
                    <th>Alignment</th>
                    <th>Class</th>
                    <th>Background</th>
                    <th>Species</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($characters as $char): ?>
                    <tr>
                        <td><?= htmlspecialchars($char['character_name']) ?></td>
                        <td><?= htmlspecialchars($char['level']) ?></td>
                        <td><?= htmlspecialchars($char['alignment']) ?></td>
                        <td><?= htmlspecialchars($char['character_class']) ?></td>
                        <td><?= htmlspecialchars($char['background']) ?></td>
                        <td><?= htmlspecialchars($char['species']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.addEventListener('DOMContentLoaded', () => {
        const toastEl = document.getElementById('successToast');
        if (toastEl) {
            const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
            toast.show();
        }
    });
</script>

</body>
</html>

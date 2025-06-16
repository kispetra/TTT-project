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

$editable_stats = [
    'Farmer' => ['base_strength', 'base_constitution', 'base_wisdom'],
    'Charlatan' => ['base_charisma', 'base_dexterity', 'base_intelligence'],
    
];

$allowed_stats = $editable_stats[$char['background']] ?? [];
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

        <form method="POST" action="update-stats.php" id="statForm">
            <?php
            // Definiraj koje statove je dopušteno uređivati
            $editable_stats = [
                'Farmer' => ['base_strength', 'base_constitution', 'base_wisdom'],
                'Charlatan' => ['base_charisma', 'base_dexterity', 'base_intelligence'],
                // dodaj ostale ako treba
            ];

            $allowed_stats = $editable_stats[$char['background']] ?? [];

            // Helper funkcija
            function renderStatField($label, $field, $char, $allowed_stats) {
                $value = (int)$char[$field];
                $modifier = ($value < 10) ? ceil(($value - 10) / 2) : floor(($value - 10) / 2);
                $prettyLabel = ucfirst(str_replace('base_', '', $field));

                if (in_array($field, $allowed_stats)) {
                    $min = $value;
                    $max = $value + 3;
                    echo <<<HTML
                        <div class="mb-2">
                            <label><strong>$prettyLabel:</strong></label>
                            <input type="number"
                                   name="$field"
                                   class="form-control stat-input"
                                   value="$value"
                                   data-base="$value"
                                   min="$min"
                                   max="$max">
                            <small class="text-muted">$modifier</small>
                        </div>
                    HTML;
                } else {
                    echo <<<HTML
                        <p><strong>$prettyLabel:</strong> $value ($modifier)</p>
                    HTML;
                }
            }

            renderStatField('Strength', 'base_strength', $char, $allowed_stats);
            renderStatField('Dexterity', 'base_dexterity', $char, $allowed_stats);
            renderStatField('Constitution', 'base_constitution', $char, $allowed_stats);
            renderStatField('Intelligence', 'base_intelligence', $char, $allowed_stats);
            renderStatField('Wisdom', 'base_wisdom', $char, $allowed_stats);
            renderStatField('Charisma', 'base_charisma', $char, $allowed_stats);
            ?>

            <?php if (!empty($allowed_stats)): ?>
                <button type="submit" class="btn btn-primary mt-3" id="submitBtn" disabled>Save Changes</button>
            <?php endif; ?>
        </form>

        <?php if (!empty($char['subspecies'])): ?>
            <p><strong>Subspecies:</strong> <?= htmlspecialchars($char['subspecies']) ?></p>
        <?php endif; ?>

        <a href="profile.php" class="btn btn-secondary mt-3">Back to Profile</a>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const inputs = document.querySelectorAll('.stat-input');
        const submitBtn = document.getElementById('submitBtn');

        function validateTotal() {
            let totalIncrease = 0;

            inputs.forEach(input => {
                const base = parseInt(input.dataset.base);
                const current = parseInt(input.value);
                if (!isNaN(current)) {
                    totalIncrease += (current - base);
                }
            });

            submitBtn.disabled = totalIncrease > 3 || totalIncrease <= 0;
        }

        inputs.forEach(input => {
            input.addEventListener('input', validateTotal);
        });

        validateTotal();
    });
    </script>
</body>
</html>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('.stat-input');
    const submitBtn = document.getElementById('submitBtn');

    function validateTotal() {
        let totalIncrease = 0;

        inputs.forEach(input => {
            const base = parseInt(input.dataset.base);
            const current = parseInt(input.value);
            if (!isNaN(current)) {
                totalIncrease += (current - base);
            }
        });

        submitBtn.disabled = totalIncrease > 3 || totalIncrease < 1;
    }

    inputs.forEach(input => {
        input.addEventListener('input', validateTotal);
    });

    validateTotal();
});
</script>


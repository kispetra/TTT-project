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

function abilityModifier($score) {
    return ($score < 10) ? ceil(($score - 10) / 2) : floor(($score - 10) / 2);
}

$dexMod = abilityModifier((int)$char['base_dexterity']);
$wisMod = abilityModifier((int)$char['base_wisdom']);
$isMonk = strtolower($char['character_class']) === 'monk';

function getArmorACValues($dexMod, $wisMod, $isMonk) {
    return [
        'Padded Armor' => 11 + $dexMod,
        'Leather Armor' => 11 + $dexMod,
        'Studded Leather Armor' => 12 + $dexMod,
        'Hide Armor' => 12 + min($dexMod, 2),
        'Chain Shirt' => 13 + min($dexMod, 2),
        'Scale Mail' => 14 + min($dexMod, 2),
        'Breastplate' => 14 + min($dexMod, 2),
        'Half Plate Armor' => 15 + min($dexMod, 2),
        'Ring Mail' => 14,
        'Chain Mail' => 16,
        'Splint Armor' => 17,
        'Plate Armor' => 18,
        'Shield' => 2,
        'None' => $isMonk ? (10 + $dexMod + $wisMod) : (10 + $dexMod),
    ];
}

$armorACValues = getArmorACValues($dexMod, $wisMod, $isMonk);
$selected_armor = $char['armor'] ?? 'None';
$base_ac = $armorACValues[$selected_armor] ?? (10 + $dexMod);

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
        <input type="hidden" name="character_id" value="<?= $char['character_id'] ?>">
        <?php
        $editable_stats = [
            'Farmer' => ['base_strength', 'base_constitution', 'base_wisdom'],
            'Charlatan' => ['base_charisma', 'base_dexterity', 'base_intelligence'],
            // other backgrounds
            'Acolyte'     => ['base_wisdom', 'base_charisma', 'base_intelligence'],
            'Artisan'     => ['base_intelligence', 'base_wisdom', 'base_constitution'],
            'Criminal'    => ['base_dexterity', 'base_charisma', 'base_intelligence'],
            'Entertainer' => ['base_charisma', 'base_dexterity', 'base_strength'],
            'Guard'       => ['base_strength', 'base_dexterity', 'base_constitution'],
            'Guide'       => ['base_wisdom', 'base_dexterity', 'base_intelligence'],
            'Hermit'      => ['base_wisdom', 'base_intelligence', 'base_constitution'],
            'Merchant'    => ['base_charisma', 'base_intelligence', 'base_wisdom'],
            'Noble'       => ['base_charisma', 'base_intelligence', 'base_wisdom'],
            'Sage'        => ['base_intelligence', 'base_wisdom', 'base_charisma'],
            'Sailor'      => ['base_strength', 'base_dexterity', 'base_constitution'],
            'Scribe'      => ['base_intelligence', 'base_wisdom', 'base_charisma'],
            'Soldier'     => ['base_strength', 'base_constitution', 'base_dexterity'],
            'Wayfarer'    => ['base_wisdom', 'base_dexterity', 'base_constitution'],
        ];

        $allowed_stats = $editable_stats[$char['background']] ?? [];

        function EnableStatFields($field, $char, $allowed_stats) {
            $value = (int)$char[$field];
            $modifier = ($value < 10) ? ceil(($value - 10) / 2) : floor(($value - 10) / 2);
            $prettyLabel = ucfirst(str_replace('base_', '', $field));

            if (in_array($field, $allowed_stats)) {
                echo <<<HTML
                    <div class="mb-2">
                        <label><strong>$prettyLabel:</strong></label>
                        <input type="number"
                               name="$field"
                               class="form-control stat-input"
                               value="$value"
                               data-base="$value"
                               min="$value"
                               max="{$value}"
                               onkeydown="return false">
                        <small class="text-muted">($modifier)</small>
                    </div>
                HTML;
            } else {
                echo <<<HTML
                    <p><strong>$prettyLabel:</strong> $value ($modifier)</p>
                HTML;
            }
        }

        foreach (['base_strength', 'base_dexterity', 'base_constitution', 'base_intelligence', 'base_wisdom', 'base_charisma'] as $stat) {
            EnableStatFields($stat, $char, $allowed_stats); }
        ?>

        <div id="pointsLeft" class="mb-3 fw-bold text-primary">
            Points left: 3
        </div>


       <?php if ($isMonk): ?>
            <div class="mb-3">
                <label class="form-label">Armor</label>
                <select class="form-select" id="armor" name="armor" disabled>
                    <option value="None" selected>No Armor (Monks cannot wear armor)</option>
                </select>
            </div>
            <p><strong>Current Armor Class (AC):</strong> <span id="current-ac"><?= 0 ?></span></p>
        <?php else: ?>
            <div class="mb-3">
                <label class="form-label">Armor</label>
                <select class="form-select" id="armor" name="armor" required>
                    <option value="None" <?= ($selected_armor === 'None') ? 'selected' : '' ?>>No Armor</option>
                    <?php foreach ($armorACValues as $armorName => $ac): ?>
                        <?php if ($armorName !== 'None'): ?>
                            <option value="<?= $armorName ?>" <?= ($selected_armor === $armorName) ? 'selected' : '' ?>>
                                <?= $armorName ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <p><strong>Current Armor Class (AC):</strong> <span id="current-ac"><?= $base_ac ?></span></p>

        <?php endif; ?>

        <div class="mb-3">
                    <label class="form-label">Weapons</label>
                    <select class="form-select" id="weapons" name="weapons" required>
                        <option value="" disabled selected>Choose Weapons</option>
                        <option value="Club">Club</option>
                        <option value="Dagger">Dagger</option>
                        <option value="Greatclub">Greatclub</option>
                        <option value="Handaxe">Handaxe</option>
                        <option value="Javelin">Javelin</option>
                        <option value="Light Hammer">Light Hammer</option>
                        <option value="Mace">Mace</option>
                        <option value="Quarterstaff">Quarterstaff</option>
                        <option value="Sickle">Sickle</option>
                        <option value="Spear">Spear</option>
                        <option value="Dart">Dart</option>
                        <option value="Light Crossbow">Light Crossbow</option>
                        <option value="Shortbow">Shortbow</option>
                        <option value="Sling">Sling</option>
                        <option value="Battleaxe">Battleaxe</option>
                        <option value="Flail">Flail</option>
                        <option value="Glaive">Glaive</option>
                        <option value="Greataxe">Greataxe</option>
                        <option value="Greatsword">Greatsword</option>
                        <option value="Halberd">Halberd</option>
                        <option value="Lance">Lance</option>
                        <option value="Longsword">Longsword</option>
                        <option value="Maul">Maul</option>
                        <option value="Morningstar">Morningstar</option>
                        <option value="Pike">Pike</option>
                        <option value="Rapier">Rapier</option>
                        <option value="Scimitar">Scimitar</option>
                        <option value="Shortsword">Shortsword</option>
                        <option value="Trident">Trident</option>
                        <option value="Warhammer">Warhammer</option>
                        <option value="War Pick">War Pick</option>
                        <option value="Whip">Whip</option>
                        <option value="Blowgun">Blowgun</option>
                        <option value="Hand Crossbow">Hand Crossbow</option>
                        <option value="Heavy Crossbow">Heavy Crossbow</option>
                        <option value="Longbow">Longbow</option>
                        <option value="Musket">Musket</option>
                        <option value="Pistol">Pistol</option>
                    </select>
                    <div id="weapon-damage" class="textsuccess"></div>
                    <div id="weapon-properties" class="textsecondary"></div>
                </div>


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
    const inputs = Array.from(document.querySelectorAll('.stat-input'));
    const submitBtn = document.getElementById('submitBtn');
    const pointsLeftDisplay = document.getElementById('pointsLeft');
    const armorSelect = document.getElementById('armor');
    const originalArmor = armorSelect ? armorSelect.value : null;

    function updateLimits() {
        const maxTotal = 3;
        const maxPerStat = 2;
        let totalIncrease = 0;

        inputs.forEach(input => {
            const base = parseInt(input.dataset.base);
            const current = parseInt(input.value);
            totalIncrease += (current - base);
        });

        inputs.forEach(input => {
            const base = parseInt(input.dataset.base);
            const current = parseInt(input.value);
            const otherIncrease = totalIncrease - (current - base);
            const allowedIncrease = Math.min(maxPerStat, maxTotal - otherIncrease);
            const maxAllowedValue = base + allowedIncrease;

            input.min = base;
            input.max = maxAllowedValue;
            if (current > maxAllowedValue) {
                input.value = maxAllowedValue;
            }
        });

        const remaining = maxTotal - totalIncrease;
        pointsLeftDisplay.textContent = `Points left: ${remaining}`;
        pointsLeftDisplay.classList.toggle('text-danger', remaining === 0);
        pointsLeftDisplay.classList.toggle('text-primary', remaining > 0);

        const statsChanged = totalIncrease > 0;
        const armorChanged = armorSelect && armorSelect.value !== originalArmor;

        submitBtn.disabled = !(statsChanged || armorChanged);
    }

    inputs.forEach(input => {
        input.addEventListener('input', updateLimits);
    });

    if (armorSelect) {
        armorSelect.addEventListener('change', updateLimits);
    }

    updateLimits();

        // ==== Weapon Section ====
    const weaponSelect = document.getElementById('weapons');
    const damageDisplay = document.getElementById('weapon-damage');
    const propertiesDisplay = document.getElementById('weapon-properties');

    const weaponDamageMap = {
        "Club": "1d4 Bludgeoning",
        "Dagger": "1d4 Piercing",
        "Greatclub": "1d8 Bludgeoning",
        "Handaxe": "1d6 Slashing",
        "Javelin": "1d6 Piercing",
        "Light Hammer": "1d4 Bludgeoning",
        "Mace": "1d6 Bludgeoning",
        "Quarterstaff": "1d6 Bludgeoning",
        "Sickle": "1d4 Slashing",
        "Spear": "1d6 Piercing",
        "Dart": "1d4 Piercing",
        "Light Crossbow": "1d8 Piercing",
        "Shortbow": "1d6 Piercing",
        "Sling": "1d4 Bludgeoning",
        "Battleaxe": "1d8 Slashing",
        "Flail": "1d8 Bludgeoning",
        "Glaive": "1d10 Slashing",
        "Greataxe": "1d12 Slashing",
        "Greatsword": "2d6 Slashing",
        "Halberd": "1d10 Slashing",
        "Lance": "1d12 Piercing",
        "Longsword": "1d8 Slashing",
        "Maul": "2d6 Bludgeoning",
        "Morningstar": "1d8 Piercing",
        "Pike": "1d10 Piercing",
        "Rapier": "1d8 Piercing",
        "Scimitar": "1d6 Slashing",
        "Shortsword": "1d6 Piercing",
        "Trident": "1d6 Piercing",
        "Warhammer": "1d8 Bludgeoning",
        "War Pick": "1d8 Piercing",
        "Whip": "1d4 Slashing",
        "Blowgun": "1 Piercing",
        "Hand Crossbow": "1d6 Piercing",
        "Heavy Crossbow": "1d10 Piercing",
        "Longbow": "1d8 Piercing",
        "Musket": "1d12 Piercing",
        "Pistol": "1d10 Piercing"
    };

    const weaponProperties = {
        "Club":["Light"],
        "Dagger": ["Finesse", "Light", "Thrown"],
        "Greatclub": ["Two-Handed"],
        "Handaxe": ["Light", "Thrown"],
        "Light Hammer": ["Light", "Thrown"],
        "Mace": [""],
        "Quarterstaff": ["Versatile"],
        "Sickle": ["Light"],
        "Spear": ["Thrown", "Versatile"],
        "Dart": ["Finesse", "Thrown"],
        "Light Crossbow": ["Ammunition", "Loading", "Two-Handed"],
        "Shortbow": ["Ammunition", "Two-Handed"],
        "Sling": ["Ammunition"],
        "Flail": [""],
        "Glaive": ["Heavy", "Reach", "Two-Handed"],
        "Greataxe": ["Heavy","Two-Handed"],
        "Greatsword": ["Heavy", "Two-Handed"],
        "Halberd": ["Heavy", "Reach", "Two-Handed"],
        "Lance": ["Heavy", "Reach", "Two-Handed"],
        "Longsword": ["Versatile"],
        "Maul": ["Heavy", "Two-Handed"],
        "Morningstar": [""],
        "Pike": ["Heavy", "Reach", "Two-Handed"],
        "Rapier": ["Finesse"],
        "Scimitar": ["Finesse", "Light"],
        "Shortsword": ["Finesse", "Light"],
        "Trident": ["Thrown", "Versatile"],
        "Warhammer": ["Versatile"],
        "War Pick": ["Versatile"],
        "Whip": ["Finesse", "Reach"],
        "Blowgun": ["Ammunition", "Loading"],
        "Hand Crossbow": ["Ammunition", "Light", "Loading"],
        "Heavy Crossbow": ["Ammunition", "Heavy", "Loading", "Two-Handed"],
        "Longbow": ["Ammunition", "Heavy", "Two-Handed"],
        "Musket": ["Ammunition", "Loading", "Two-Handed"],
        "Pistol": ["Ammunition", "Loading"],
    };

    weaponSelect.addEventListener('change', () => {
        const selectedWeapon = weaponSelect.value;

        if (weaponDamageMap[selectedWeapon]) {
            damageDisplay.innerHTML = `<strong>Damage:</strong> ${weaponDamageMap[selectedWeapon]}`;

            const props = weaponProperties[selectedWeapon] || [];
            if (props.length > 0) {
                propertiesDisplay.innerHTML = `<strong>Properties:</strong> ${props.join(', ')}`;
            } else {
                propertiesDisplay.innerHTML = `<strong>Properties:</strong> None`;
            }
        } else {
            damageDisplay.textContent = "";
            propertiesDisplay.textContent = "";
        }
    });

});

</script>
</body>
</html> 



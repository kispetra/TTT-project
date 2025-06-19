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


$allWeapons = ['None', 'Club', 'Dagger', 'Greatclub', 'Handaxe', 'Javelin', 'Light Hammer', 'Mace', 'Quarterstaff', 'Sickle', 'Spear', 'Dart', 
                "Light Crossbow", "Shortbow", "Sling", "Battleaxe", "Flail", "Glaive", "Greataxe", "Greatsword", "Halberd", "Lance", "Longsword", 
                "Maul", "Morningstar", "Pike", "Rapier", "Scimitar", "Shortsword", "Trident", "Warhammer", "War Pick", "Whip", "Blowgun", "Hand Crossbow", 
                "Heavy Crossbow", "Longbow", "Musket", "Pistol" ];

if ($isMonk) {
    array_unshift($allWeapons, 'Fist'); 
}

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
            $modifier = abilityModifier($value);
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
                    <option value="" disabled <?= empty($char['weapons']) ? 'selected' : '' ?>>Choose Weapon</option>
                    <?php foreach ($allWeapons as $weapon): ?>
                        <option value="<?= $weapon ?>" <?= ($char['weapons'] === $weapon) ? 'selected' : '' ?>>
                            <?= $weapon ?>
                        </option>
                    <?php endforeach; ?>
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
    const characterLevel = <?= (int)$char['level'] ?>;

    const weaponSelect = document.getElementById('weapons');
    const damageDisplay = document.getElementById('weapon-damage');
    const propertiesDisplay = document.getElementById('weapon-properties');
    const originalWeapon = weaponSelect ? weaponSelect.value : null;

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
        const weaponChanged = weaponSelect && weaponSelect.value !== originalWeapon;

        submitBtn.disabled = !(statsChanged || armorChanged || weaponChanged);
    }

    inputs.forEach(input => {
        input.addEventListener('input', updateLimits);
    });

    if (armorSelect) {
        armorSelect.addEventListener('change', updateLimits);
    }

    if (weaponSelect) {
        weaponSelect.addEventListener('change', updateLimits);
    }

    updateLimits();

    const currentACDisplay = document.getElementById('current-ac');

    const dexMod = <?= $dexMod ?>;
    const wisMod = <?= $wisMod ?>;
    const isMonk = <?= $isMonk ? 'true' : 'false' ?>;

    const armorACValues = <?= json_encode($armorACValues) ?>;

    if (armorSelect) {
        armorSelect.addEventListener('change', function () {
            const selected = armorSelect.value;
            const newAC = armorACValues[selected] ?? (10 + dexMod);
            currentACDisplay.textContent = newAC;
        });
    }


    const weaponDamageMap = {
        "None" : "None",
        "Fist" : "1d6 Bludgeoning",
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
        "None" : ["None"],
        "Fist" : [""],  
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

        if (selectedWeapon === "Fist") {
            let damage;
            if (characterLevel >= 17) {
                damage = "1d12";
            } else if (characterLevel >= 11) {
                damage = "1d10";
            } else if (characterLevel >= 5) {
                damage = "1d8";
            } else {
                damage = "1d6";
            }

            damageDisplay.innerHTML = `<strong>Damage:</strong> ${damage}`;
            propertiesDisplay.innerHTML = `<strong>Properties:</strong> Monk Only, Unarmed`;
        } else if (weaponDamageMap[selectedWeapon]) {
            damageDisplay.innerHTML = `<strong>Damage:</strong> ${weaponDamageMap[selectedWeapon]}`;

            const props = weaponProperties[selectedWeapon] || [];
            if (props.length > 0) {
                propertiesDisplay.innerHTML = `<strong>Properties:</strong> ${props.join(', ')}`;
            } else {
                propertiesDisplay.innerHTML = `<strong>Properties:</strong> None`;
            }
        }
    });

});

</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('#update-form'); // Prilagodi ako imaš drugačiji ID
    const saveButton = document.querySelector('#submitBtn'); // Pretpostavka: button ima id="save-button"
    const initialData = new FormData(form);

    function formChanged() {
        const currentData = new FormData(form);
        for (let [key, value] of currentData.entries()) {
            if (initialData.get(key) !== value) {
                return true;
            }
        }
        return false;
    }

    form.addEventListener('input', () => {
        if (formChanged()) {
            saveButton.disabled = false;
        } else {
            saveButton.disabled = true;
        }
    });
});
</script>




</body>
</html> 



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

function abilityModifier($score)
{
    return ($score < 10) ? ceil(($score - 10) / 2) : floor(($score - 10) / 2);
}

$dexMod = abilityModifier((int)$char['base_dexterity']);
$wisMod = abilityModifier((int)$char['base_wisdom']);
$isMonk = strtolower($char['character_class']) === 'monk';

function getArmorACValues($dexMod, $wisMod, $isMonk)
{
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
$selected_weapon = $char['weapons'] ?? 'None';
$weapon_damage = $char['weapon_damage'] ?? '';
$weapon_properties = $char['weapon_properties'] ?? '';
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
        <?php if (!empty($char['subspecies'])): ?>
            <p><strong>Subspecies:</strong> <?= htmlspecialchars($char['subspecies']) ?></p>
        <?php endif; ?>

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

            function EnableStatFields($field, $char, $allowed_stats)
            {
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
                        <small class="text-muted">(<span class="modifier-display">{$modifier}</span>)</small>
                    </div>
                HTML;
                } else {
                    echo <<<HTML
                    <p><strong>$prettyLabel:</strong> $value ($modifier)</p>
                HTML;
                }
            }

            foreach (['base_strength', 'base_dexterity', 'base_constitution', 'base_intelligence', 'base_wisdom', 'base_charisma'] as $stat) {
                EnableStatFields($stat, $char, $allowed_stats);
            }
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
                    <option value="Club" <?= ($selected_weapon === 'Club') ? 'selected' : '' ?>>Club</option>
                    <option value="Dagger" <?= ($selected_weapon === 'Dagger') ? 'selected' : '' ?>>Dagger</option>
                    <option value="Greatclub" <?= ($selected_weapon === 'Greatclub') ? 'selected' : '' ?>>Greatclub</option>
                    <option value="Handaxe" <?= ($selected_weapon === 'Handaxe') ? 'selected' : '' ?>>Handaxe</option>
                    <option value="Javelin" <?= ($selected_weapon === 'Javelin') ? 'selected' : '' ?>>Javelin</option>
                    <option value="Light Hammer" <?= ($selected_weapon === 'Light Hammer') ? 'selected' : '' ?>>Light Hammer</option>
                    <option value="Mace" <?= ($selected_weapon === 'Mace') ? 'selected' : '' ?>>Mace</option>
                    <option value="Quarterstaff" <?= ($selected_weapon === 'Quarterstaff') ? 'selected' : '' ?>>Quarterstaff</option>
                    <option value="Sickle" <?= ($selected_weapon === 'Sickle') ? 'selected' : '' ?>>Sickle</option>
                    <option value="Spear" <?= ($selected_weapon === 'Spear') ? 'selected' : '' ?>>Spear</option>
                    <option value="Dart" <?= ($selected_weapon === 'Dart') ? 'selected' : '' ?>>Dart</option>
                    <option value="Light Crossbow" <?= ($selected_weapon === 'Light Crossbow') ? 'selected' : '' ?>>Light Crossbow</option>
                    <option value="Shortbow" <?= ($selected_weapon === 'Shortbow') ? 'selected' : '' ?>>Shortbow</option>
                    <option value="Sling" <?= ($selected_weapon === 'Sling') ? 'selected' : '' ?>>Sling</option>
                    <option value="Battleaxe" <?= ($selected_weapon === 'Battleaxe') ? 'selected' : '' ?>>Battleaxe</option>
                    <option value="Flail" <?= ($selected_weapon === 'Flail') ? 'selected' : '' ?>>Flail</option>
                    <option value="Glaive" <?= ($selected_weapon === 'Glaive') ? 'selected' : '' ?>>Glaive</option>
                    <option value="Greataxe" <?= ($selected_weapon === 'Greataxe') ? 'selected' : '' ?>>Greataxe</option>
                    <option value="Greatsword" <?= ($selected_weapon === 'Greatsword') ? 'selected' : '' ?>>Greatsword</option>
                    <option value="Halberd" <?= ($selected_weapon === 'Halberd') ? 'selected' : '' ?>>Halberd</option>
                    <option value="Lance" <?= ($selected_weapon === 'Lance') ? 'selected' : '' ?>>Lance</option>
                    <option value="Longsword" <?= ($selected_weapon === 'Longsword') ? 'selected' : '' ?>>Longsword</option>
                    <option value="Maul" <?= ($selected_weapon === 'Maul') ? 'selected' : '' ?>>Maul</option>
                    <option value="Morningstar" <?= ($selected_weapon === 'Morningstar') ? 'selected' : '' ?>>Morningstar</option>
                    <option value="Pike" <?= ($selected_weapon === 'Pike') ? 'selected' : '' ?>>Pike</option>
                    <option value="Rapier" <?= ($selected_weapon === 'Rapier') ? 'selected' : '' ?>>Rapier</option>
                    <option value="Scimitar" <?= ($selected_weapon === 'Scimitar') ? 'selected' : '' ?>>Scimitar</option>
                    <option value="Shortsword" <?= ($selected_weapon === 'Shortsword') ? 'selected' : '' ?>>Shortsword</option>
                    <option value="Trident" <?= ($selected_weapon === 'Trident') ? 'selected' : '' ?>>Trident</option>
                    <option value="Warhammer" <?= ($selected_weapon === 'Warhammer') ? 'selected' : '' ?>>Warhammer</option>
                    <option value="War Pick" <?= ($selected_weapon === 'War Pick') ? 'selected' : '' ?>>War Pick</option>
                    <option value="Whip" <?= ($selected_weapon === 'Whip') ? 'selected' : '' ?>>Whip</option>
                    <option value="Blowgun" <?= ($selected_weapon === 'Blowgun') ? 'selected' : '' ?>>Blowgun</option>
                    <option value="Hand Crossbow" <?= ($selected_weapon === 'Hand Crossbow') ? 'selected' : '' ?>>Hand Crossbow</option>
                    <option value="Heavy Crossbow" <?= ($selected_weapon === 'Heavy Crossbow') ? 'selected' : '' ?>>Heavy Crossbow</option>
                    <option value="Longbow" <?= ($selected_weapon === 'Longbow') ? 'selected' : '' ?>>Longbow</option>
                    <option value="Musket" <?= ($selected_weapon === 'Musket') ? 'selected' : '' ?>>Musket</option>
                    <option value="Pistol" <?= ($selected_weapon === 'Pistol') ? 'selected' : '' ?>>Pistol</option>
                </select>
                <!-- prikaz -->
                <p id="weaponDamageDisplay" class="mb-0 fw-bold"></p>
                <p id="weaponPropsDisplay" class="mb-3 fst-italic"></p>

                <!-- skrivena polja koja idu u bazu -->
                <input type="hidden" id="weaponDamageInput" name="weapon_damage" value="<?= htmlspecialchars($weapon_damage) ?>">
                <input type="hidden" id="weaponPropsInput" name="weapon_properties" value="<?= htmlspecialchars($weapon_properties) ?>">
            </div>

            <button id="rollDiceBtn" type="button" class="btn btn-primary mt-2">Roll Dice</button>
            <div id="diceResult" class="mt-2 fw-bold"></div>

            <?php if (!empty($allowed_stats)): ?>
                <button type="submit" class="btn btn-primary mt-3" id="submitBtn" disabled>Save Changes</button>
            <?php endif; ?>
        </form>

        <a href="profile.php" class="btn btn-secondary mt-3">Back to Profile</a>
    </div>

    <script>
        const CHAR_LEVEL = <?= (int)$char['level']; ?>;
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inputs = Array.from(document.querySelectorAll('.stat-input'));
            const submitBtn = document.getElementById('submitBtn');
            const pointsLeftDisplay = document.getElementById('pointsLeft');
            const armorSelect = document.getElementById('armor');
            const originalArmor = armorSelect ? armorSelect.value : null;

            const weaponSelect = document.getElementById('weapons');
            const originalWeapon = weaponSelect.value;

            const diceResult = document.getElementById('diceResult');
            const rollDiceBtn = document.getElementById('rollDiceBtn');


            function updateLimits() {
                const canImprove = (CHAR_LEVEL % 4 === 0);

                inputs.forEach(inp => {
                    inp.disabled = !canImprove;
                });

                let totalIncrease = 0;

                inputs.forEach(input => {
                    const base = parseInt(input.dataset.base);
                    const current = parseInt(input.value);
                    totalIncrease += (current - base);
                });

                const maxTotal = 3;
                const maxPerStat = 2;

                if (canImprove) {
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
                } else {
                    // Reset stat inputs to base if not improvable
                    inputs.forEach(inp => inp.value = inp.dataset.base);
                }

                // Update modifiers
                inputs.forEach(input => {
                    const value = parseInt(input.value);
                    const modifier = (value < 10) ? Math.ceil((value - 10) / 2) : Math.floor((value - 10) / 2);
                    const modifierDisplay = input.parentElement.querySelector('.modifier-display');
                    if (modifierDisplay) {
                        modifierDisplay.textContent = modifier >= 0 ? `+${modifier}` : modifier;
                    }
                });

                const remaining = maxTotal - totalIncrease;
                const statsChanged = totalIncrease > 0;
                const allPointsUsed = remaining === 0;

                const armorChanged = armorSelect && armorSelect.value !== originalArmor;
                const weaponChanged = weaponSelect.value !== originalWeapon;

                if (canImprove) {
                    pointsLeftDisplay.textContent = `Points left: ${remaining}`;
                    pointsLeftDisplay.classList.toggle('text-danger', remaining === 0);
                    pointsLeftDisplay.classList.toggle('text-primary', remaining > 0);
                } else {
                    pointsLeftDisplay.textContent = 'No ability points at this level';
                    pointsLeftDisplay.classList.add('text-secondary');
                    pointsLeftDisplay.classList.remove('text-danger', 'text-primary');
                }

                if (canImprove) {
                    // Level je dijeljiv s 4, obavezno iskoristiti sve bodove i promijeniti barem jedan stat
                    submitBtn.disabled = !(allPointsUsed && statsChanged);
                } else {
                    // Level NIJE dijeljiv s 4 – statovi su zaključani, ali armor/weapon se mogu promijeniti
                    const weaponChanged = weaponSelect.value !== originalWeapon;
                    submitBtn.disabled = !(armorChanged || weaponChanged);
                }
            }

            inputs.forEach(input => {
                input.addEventListener('input', updateLimits);
            });


            if (armorSelect) {
                armorSelect.addEventListener('change', updateLimits);
            }

            updateLimits();

            // ==== Weapon Section ====
            const dmgDisp = document.getElementById('weaponDamageDisplay');
            const propsDisp = document.getElementById('weaponPropsDisplay');
            const dmgInputHidden = document.getElementById('weaponDamageInput');
            const propsInputHidden = document.getElementById('weaponPropsInput');

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
                "Club": ["Light"],
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
                "Greataxe": ["Heavy", "Two-Handed"],
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

            let dieValue = null;

            function extractDieValue(dmg) {
                const m = dmg.match(/d(\d+)/);
                return m ? parseInt(m[1], 10) : null;
            }

            function renderWeapon() {
                const w = weaponSelect.value;
                const dmg = weaponDamageMap[w] || '';
                const prp = weaponProperties[w] || [];

                dmgDisp.textContent = dmg ? `Damage: ${dmg}` : '';
                propsDisp.textContent = prp.length ? `Properties: ${prp.join(', ')}` : '';

                dmgInputHidden.value = dmg;
                propsInputHidden.value = prp.join(', ');

                dieValue = extractDieValue(dmg);
            }

            weaponSelect.addEventListener('change', renderWeapon);
            if (weaponSelect.value) renderWeapon();

            function rollDie(sides) {
                return Math.floor(Math.random() * sides) + 1;
            }

            rollDiceBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const r1 = rollDie(20);
                const r2 = rollDie(20);
                const r3 = dieValue ? rollDie(dieValue) : '?';
                diceResult.textContent = `🎲 Rolls: ${r1}, ${r2}, ${r3}`;
            });
        });
    </script>
</body>

</html>
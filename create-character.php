<?php
session_start();

require_once 'db.php';

$classData = [
    "Barbarian" => [
        "primary_ability" => "Strength",
        "skill_proficiencies" => ["Animal Handling", "Athletics", "Intimidation", "Nature", "Perception", "Survival"],
        "starting_equipment" => ["Greataxe, 4 Handaxes, Explorer's Pack, 15 GP", "75 GP"],
        "hit_point_die" => "d12",
        "saving_throw_proficiencies" => ["Strength", "Constitution"],
        "weapon_proficiencies" => ["Simple", "Martial"],
        "armor_training" => ["Light armor", "Medium armor", "Shields"]
    ],
    "Bard" => [
        "primary_ability" => "Charisma",
        "skill_proficiencies" => ["Acrobatics", "Animal Handling", "Arcana", "Athletics", "Deception", "History", "Insight", "Intimidation", "Investigation", "Medicine", "Nature", "Perception", "Performance", "Persuasion", "Religion", "Sleight of Hand", "Stealth",  "Survival"],
        "starting_equipment" => ["Leather Armor, 2 Daggers, Musical Instrument of your choice, Entertainer's Pack, 19 GP", "90 GP"],
        "hit_point_die" => "d8",
        "saving_throw_proficiencies" => ["Dexterity", "Charisma"],
        "weapon_proficiencies" => "Simple",
        "armor_training" => "Light armor",
        "tool proficiencies" => ["Bagpipes", "Drum", "Dulcimer", "Flute", "Horn", "Lute", "Lyre", "Pan flute", "Shawm", "Viol"]
    ],
    "Cleric" => [
        "primary_ability" => "Wisdom",
        "skill_proficiencies" => ["History", "Insight", "Medicine", "Persuasion", "Religion"],
        "starting_equipment" => ["Chain Shirt, Shield, Mace, Holy Symbol, Priest's Pack, 7 GP", "110 GP"],
        "hit_point_die" => "d8",
        "saving_throw_proficiencies" => ["Wisdom", "Charisma"],
        "weapon_proficiencies" => "Simple",
        "armor_training" => ["Light armor", "Medium armor"]
    ],
    "Druid" => [
        "primary_ability" => "Wisdom",
        "skill_proficiencies" => ["Arcana", "Animal Handling", "Insight", "Medicine", "Nature", "Perception", "Religion", "Survival"],
        "starting_equipment" => ["Leather Armor, Shield, Sickle, Druidic Focus (Quarterstaff), Explorer's Pack, Herbalism Kit, 9 GP", "50 GP"],
        "hit_point_die" => "d8",
        "saving_throw_proficiencies" => ["Intelligence", "Wisdom"],
        "weapon_proficiencies" => "Simple",
        "armor_training" => ["Light armor", "Shields"],
        "tool_proficiencies" => "Herbalism Kit"
    ],
    "Fighter" => [
        "primary_ability" => ["Strength", "Dexterity"],
        "skill_proficiencies" => ["Acrobatics", "Animal Handling", "Athletics", "History", "Insight", "Intimidation", "Persuasion", "Perception", "Survival"],
        "starting_equipment" => ["Chain Mail, Greatsword, Flail, 8 Javelins, Dungeoneer's Pack, 4 GP", "Studded Leather Armor, Scimitar, Shortsword, Longbow, 20 Arows, Quiver, Dungeoneer's Pack, 11 GP", "155 GP"],
        "hit_point_die" => "d10",
        "saving_throw_proficiencies" => ["Strength", "Constitution"],
        "weapon_proficiencies" => ["Simple", "Martial"],
        "armor_training" => ["Light armor", "Medium Armor", "Heavy Armor", "Shields"]
    ],
    "Monk" => [
        "primary_ability" => ["Dexterity", "Wisdom"],
        "skill_proficiencies" => ["Acrobatics", "Athletics", "History", "Insight", "Religion", "Stealth"],
        "starting_equipment" => ["Spear, 5 Daggers, Artisan's Tools, Musical Instrument chosen for the tool proficiency above, Explorer's Pack, 11 GP", "50 GP"],
        "hit_point_die" => "d8",
        "saving_throw_proficiencies" => ["Strength", "Dexterity"],
        "weapon_proficiencies" => ["Simple", "Martial weapons with Light property"],
        "armor_training" => "None",
        "tool_proficiencies" => ["Choose one type of Artisan's Tools", "Musical Instrument"]
    ],
    "Paladin" => [
        "primary_ability" => ["Strength", "Charisma"],
        "skill_proficiencies" => ["Athletics", "Insight", "Intimidation", "Medicine", "Persuasion", "Religion"],
        "starting_equipment" => ["Chain Mail, Shield, Longsword, 6 Javelins, Holy Symbol, Priest's Pack, 9 GP", "150 GP"],
        "hit_point_die" => "d10",
        "saving_throw_proficiencies" => ["Wisdom", "Charisma"],
        "weapon_proficiencies" => ["Simple", "Martial"],
        "armor_training" => ["Light armor", "Medium Armor", "Heavy Armor", "Shields"],
    ],
    "Ranger" => [
        "primary_ability" => ["Dexterity", "Wisdom"],
        "skill_proficiencies" => ["Animal Handling", "Athletics", "Insight", "Investigation", "Nature", "Perception", "Stealth", "Survival"],
        "starting_equipment" => ["Studded Leather Armor, Scimitar, Shortsword, Longbow, 20 Arrows, Quiver, Druidic Focus (sprig of mistletoe), Explorer's Pack, 7 GP", "150 GP"],
        "hit_point_die" => "d10",
        "saving_throw_proficiencies" => ["Strength", "Dexterity"],
        "weapon_proficiencies" => ["Simple", "Martial"],
        "armor_training" => ["Light armor", "Medium Armor", "Shields"],
    ],
    "Rogue" => [
        "primary_ability" => "Dexterity",
        "skill_proficiencies" => ["Acrobatics", "Athletics", "Deception", "Insight", "Intimidation", "Investigation", "Perception", "Persuasion", "Sleight of Hand", "Stealth"],
        "starting_equipment" => ["Leather Armor, 2 Daggers, Shortsword, Shortbow, Longbow, 20 Arrows, Quiver, Thieves' Tools, Burglar's Pack, 8 GP", "100 GP"],
        "hit_point_die" => "d8",
        "saving_throw_proficiencies" => ["Dexterity", "Intelligence"],
        "weapon_proficiencies" => ["Simple", "Martial weapons with Finesse or Light property"],
        "armor_training" => "Light armor",
        "tool_proficiencies" => "Thieves' Tools"
    ],
    "Sorcerer" => [
        "primary_ability" => "Charisma",
        "skill_proficiencies" => ["Arcana", "Deception", "Insight", "Intimidation", "Persuasion", "Religion"],
        "starting_equipment" => ["Spear, 2 Daggers, Arcane Focus (crystal), Dungeoneer's Pack, 28 GP", "50 GP"],
        "hit_point_die" => "d6",
        "saving_throw_proficiencies" => ["Constitution", "Charisma"],
        "weapon_proficiencies" => "Simple",
        "armor_training" => "None"
    ],
    "Warlock" => [
        "primary_ability" => "Charisma",
        "skill_proficiencies" => ["Arcana", "Deception", "History", "Intimidation", "Investigation", "Nature", "Religion"],
        "starting_equipment" => ["Leather Armor, Sickle, 2 Daggers, Arcane Focus (orb), Book (occult lore), Scholar's Pack, 15 GP", "100 GP"],
        "hit_point_die" => "d8",
        "saving_throw_proficiencies" => ["Wisdom", "Charisma"],
        "weapon_proficiencies" => "Simple",
        "armor_training" => "Light armor"
    ],
    "Wizard" => [
        "primary_ability" => "Intelligence",
        "skill_proficiencies" => ["Arcana", "History", "Insight", "Investigation", "Medicine", "Nature", "Religion"],
        "starting_equipment" => ["2 Daggers, Arcane Focus (Quarterstaff), Robe, Spellbook, Scholar's Pack, 5 GP", "55 GP"],
        "hit_point_die" => "d6",
        "saving_throw_proficiencies" => ["Intelligence", "Wisdom"],
        "weapon_proficiencies" => "Simple",
        "armor_training" => "None"
    ]
];

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

            $character_id = $stmt->insert_id;
            $_SESSION['success_msg'] = "Character successfully created!";
            $_SESSION['created_character_id'] = $character_id;

            // Dodaj class features
            $primary_ability = $classData[$character_class]['primary_ability'] ?? null;
            $hit_point_die = $classData[$character_class]['hit_point_die'] ?? null;
            $saving_throw_proficiencies = $classData[$character_class]['saving_throw_proficiencies'] ?? null;
            $weapon_proficiencies = $classData[$character_class]['weapon_proficiencies'] ?? null;
            $armor_training = $classData[$character_class]['armor_training'] ?? null;
            $tool_proficiencies = $classData[$character_class]['tool_proficiencies'] ?? null;
            $selected_skills = $_POST['skill_proficiencies'] ?? [];
            $selected_equipment = $_POST['starting_equipment'] ?? [];

            $skills_str = implode(", ", $selected_skills);
            $equipment_str = implode(", ", $selected_equipment);

            $saving_throws_str = is_array($saving_throw_proficiencies) ? implode(", ", $saving_throw_proficiencies) : $saving_throw_proficiencies;
            $weapon_profs_str = is_array($weapon_proficiencies) ? implode(", ", $weapon_proficiencies) : $weapon_proficiencies;
            $armor_str = is_array($armor_training) ? implode(", ", $armor_training) : $armor_training;
            $tool_str = is_array($tool_proficiencies) ? implode(", ", $tool_proficiencies) : $tool_proficiencies;
            $hit_die = $hit_point_die ?? null;

            $feature_query = "INSERT INTO class_features (character_id, primary_ability, skill_proficiencies, starting_equipment, 
                        hit_point_die, saving_throw_proficiencies, weapon_proficiencies, armor_training, tool_proficiencies) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $feature_stmt = $con->prepare($feature_query);
            $feature_stmt->bind_param(
                "issssssss",
                $character_id,
                $primary_ability,
                $skills_str,
                $equipment_str,
                $hit_die,
                $saving_throws_str,
                $weapon_profs_str,
                $armor_str,
                $tool_str
            );

            $feature_stmt->execute();
            $feature_stmt->close();

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

                <?php
                $class = $_SESSION['character_class'] ?? '';
                $skills = $classData[$class]['skill_proficiencies'] ?? [];
                $equipment = $classData[$class]['starting_equipment'] ?? [];
                ?>

                <div class="mb-3">
                    <?php
                    $chooseCount = 2;
                    if ($class === 'Bard' || $class === 'Ranger') $chooseCount = 3;
                    elseif ($class === 'Rogue') $chooseCount = 4;
                    ?>
                    <label class="form-label">Skill Proficiencies (choose <?= $chooseCount ?>)</label>

                    <select class="form-select" id="skillProficiencies" name="skill_proficiencies[]" multiple required data-max="<?= $chooseCount ?>">
                        <?php foreach ($skills as $skill): ?>
                            <option value="<?= $skill ?>"><?= $skill ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Starting Equipment (choose 1)</label>
                    <select class="form-select" name="starting_equipment[]" multiple required>
                        <?php foreach ($equipment as $item): ?>
                            <option value="<?= $item ?>"><?= $item ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</div>
                </div>


                <div class="buttons">
                    <button type="button" class="btn btn-back" onclick="window.history.back();">Back</button>
                    <button type="submit" name="btnCreate" class="btn btn-next">Create Character</button>
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

        <script>
    document.addEventListener('DOMContentLoaded', () => {
        const skillSelect = document.getElementById('skillProficiencies');
        const max = parseInt(skillSelect.getAttribute('data-max'));

        skillSelect.addEventListener('change', function () {
            const selected = Array.from(this.selectedOptions);
            if (selected.length > max) {
                // Automatski deselektiraj posljednji odabrani
                selected[selected.length - 1].selected = false;
                alert(`You can only choose up to ${max} skill proficiencies.`);
            }
        });
    });
</script>


</body>

</html>
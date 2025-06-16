<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? '';

$success_msg = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['next'])) {
    $_SESSION['character_class'] = $_POST['character_class'] ?? '';
    $_SESSION['user_id'] = $user_id;

    session_write_close();
    header('Location: create-character.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Select Class</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./add-character-class.css">
</head>

<body>
    <div class="container mt-5">
        <h2 class="modal-header">Choose a Class</h2>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
        <?php elseif (!empty($error_msg)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <form method="post" action="add-character-class.php" id="classForm">
            <div id="classAlert" class="alert alert-danger mt-3 d-none" role="alert">
                Please select a class before proceeding.
            </div>
            <input type="hidden" name="character_class" id="selectedClass">

            <div class="class-list">
                <?php
                $classes = [
                    "Barbarian" => "Fierce warriors fueled by primal Rage, granting them immense strength and heightened senses. They lead and protect their people by boldly facing danger head-on.",
                    "Bard" => "Masters of music and magic, bards inspire allies and manipulate foes with their enchanting performances. They use creativity and charm to influence the world around them.",
                    "Cleric" => "Divine agents who channel the power of gods to heal and protect. Clerics balance destruction and restoration, serving as warriors of faith.",
                    "Druid" => "Guardians of nature who wield elemental magic and can transform into animals. Druids protect the wild and maintain balance between the natural and supernatural.",
                    "Fighter" => "Versatile combat experts skilled in weapons and tactics. Fighters adapt to any battle and protect their allies with discipline and strength.",
                    "Monk" => "Masters of martial arts harnessing inner energy called ki. Monks combine speed, precision, and spiritual focus to overcome foes.",
                    "Paladin" => "Holy knights sworn to justice and righteousness, empowered by divine magic. Paladins serve as champions of good, defending the weak and vanquishing evil.",
                    "Ranger" => "Skilled hunters and trackers attuned to the wilderness. Rangers use their survival skills and ranged attacks to outsmart and defeat enemies.",
                    "Rogue" => "Stealthy and cunning, rogues excel at infiltration, trickery, and precision strikes. They thrive in the shadows, using agility and guile to gain the upper hand.",
                    "Sorcerer" => "Innate magic users whose power comes from a magical bloodline or force within. Sorcerers wield raw arcane energy, shaping spells with their will.",
                    "Warlock" => "Magic users who gain power through pacts with otherworldly patrons. Warlocks balance dark bargains with potent spellcasting.",
                    "Wizard" => "Scholars of arcane knowledge who learn spells through study and practice. Wizards command a vast arsenal of magic with intelligence and discipline."
                ];

                foreach ($classes as $name => $description) {
                    $imagePath = "img/{$name}.jpg";
                    echo "
                    <div class='class-option d-flex align-items-center' data-class=\"$name\">
                        <img src='$imagePath' alt='$name' class='class-icon me-3'>
                        <div>
                            <h5>$name</h5>
                            <p>$description</p>
                        </div>
                    </div>
                    ";
                }
                ?>
            </div>

            <div class="buttons">
                <button type="button" class="btn btn-back" onclick="window.history.back();">Back</button>
                <button type="submit" name="next" class="btn btn-next" id="nextBtn">Next</button>
            </div>
        </form>
    </div>

    <script>
        const classOptions = document.querySelectorAll('.class-option');
        const selectedInput = document.getElementById('selectedClass');
        const nextBtn = document.getElementById('nextBtn');
        const form = document.getElementById('classForm');
        const alertBox = document.getElementById('classAlert');

        // Default styling
        nextBtn.classList.add('btn-custom-disabled');

        classOptions.forEach(option => {
            option.addEventListener('click', () => {
                classOptions.forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');
                selectedInput.value = option.dataset.class;
                nextBtn.disabled = false;
                alertBox.classList.add('d-none');
                nextBtn.classList.remove('btn-custom-disabled');
            });
        });

        form.addEventListener('submit', function(e) {
            if (!selectedInput.value) {
                e.preventDefault();
                alertBox.classList.remove('d-none');
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        });
    </script>

</body>

</html>
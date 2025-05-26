<!DOCTYPE html>
<html lang="hr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D&D Companion</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500&family=Cardo&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./home-page.css">
</head>

<body>
    <div class="header">
        <h1>D&D Companion</h1>
    </div>

    <div class="description">
        <p>Pripremi se za magične avanture! Prati svoje likove, bilježi susrete i uroni u svijet pun misterije i zmajeva.</p>
    </div>

    <div class="dice-container" onclick="rollDice()">
        <img src="./img/d20.png" alt="Dice" class="dice" id="dice-img" />
        <div class="dice-number" id="dice-number">Roll</div>
    </div>

    <div class="navbar">
        <a href="home-page.php" class="nav-icon">
            <img src="./img/home.png" alt="Home" />
        </a>
        <a href="addCharacter.php" class="nav-add">
            <img src="./img/add.png" alt="Dodaj" />
        </a>
        <a href="profile.php" class="nav-icon">
            <img src="./img/user.png" alt="Profil" />
        </a>
    </div>


    <script>
        function rollDice() {
            const dice = document.getElementById('dice-img');
            const numberDisplay = document.getElementById('dice-number');
            const roll = Math.floor(Math.random() * 20) + 1;

            dice.classList.add('roll');
            numberDisplay.textContent = '...';

            setTimeout(() => {
                dice.classList.remove('roll');
                numberDisplay.textContent = roll;
            }, 800);
        }
    </script>

</body>
</html>
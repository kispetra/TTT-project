<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    session_unset();
    session_destroy();
    session_start(); 
}

require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emailOrUsername = $_POST['emailOrUsername'];
    $password = $_POST['password'];

    $stmt = $con->prepare("SELECT user_id, first_name, last_name, username, email, password FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $emailOrUsername, $emailOrUsername);
    $stmt->execute();
    $stmt->bind_result($user_id, $first_name, $last_name, $username, $email, $hashed_password);

    if ($stmt->fetch()) {
        if (password_verify($password, $hashed_password)) {
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            header("Location: home-page.php");
            exit;
        } else {
            $login_err = "Invalid password.";
        }
    } else {
        $login_err = "Invalid username or email.";
    }
    $stmt->close();
}
$con->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - D&D</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="login.css">
</head>

<body>
<div class="main-container">
    <div class="content-wrapper">
        <img class="logo" src="./img/Dungeons-and-Dragons-logo.png" alt="Logo" loading="lazy">

        <div class="login-card">
            <h5 class="text-center mb-3">Log In</h5>
            <form method="post" action="login.php">
                <div class="mb-3">
                    <label for="emailOrUsername" class="form-label">Username or Email</label>
                    <input type="text" name="emailOrUsername" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" id="showPassword">
                        <label class="form-check-label" for="showPassword">Show Password</label>
                    </div>
                </div>
                <?php if(isset($login_err)) echo '<div class="alert alert-danger">'.$login_err.'</div>'; ?>
                <div class="d-grid">
                    <button type="submit" class="btn btn-login text-white">Log In</button>
                </div>
                <p class="text-center mt-3 mb-0">
                    <a href="register.php">Don't have an account? Register here!</a>
                </p>
            </form>
        </div>
    </div>

    <footer>
        <div>© 2025.</div>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const passwordInput = document.getElementById('password');
    const showPasswordCheckbox = document.getElementById('showPassword');
    showPasswordCheckbox.addEventListener('change', () => {
        passwordInput.type = showPasswordCheckbox.checked ? 'text' : 'password';
    });
</script>

</body>
</html>

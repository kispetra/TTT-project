<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once 'db.php';

if (isset($_POST['submit'])) {
    $first_name   = trim($_POST['first_name']);
    $last_name    = trim($_POST['last_name']);
    $username     = trim($_POST['username']);
    $email        = trim($_POST['email']);
    $password_raw = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || substr($email, -4) !== '.com') {
        $register_err = "Email must be valid and end with .com.";
    } else if (strlen($password_raw) < 7 || !preg_match('/\d/', $password_raw)) {
        $register_err = "Password must be at least 7 characters long and contain at least one number.";
    } else {
        $chk = $con->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $chk->bind_param("ss", $username, $email);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) {
            $register_err = "That username or email is already taken.";
            $chk->close();
        } else {
            $chk->close();
            $password = password_hash($password_raw, PASSWORD_BCRYPT);
            $stmt = $con->prepare("
                INSERT INTO users (first_name, last_name, username, email, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $first_name, $last_name, $username, $email, $password);
            try {
                if ($stmt->execute()) {
                    header("Location: login.php");
                    exit();
                } else {
                    $register_err = "Database error: " . $stmt->error;
                }
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() === 1062) {
                    $register_err = "Username or email already exists.";
                } else {
                    $register_err = "Unexpected database error.";
                }
            }
            $stmt->close();
        }
        $con->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="register.css">
</head>
<body>
<div class="main-container">
    <div class="content-wrapper">
        <img class="logo" src="./img/Dungeons-and-Dragons-logo.png" alt="Logo" loading="lazy">

        <div class="register-card">
            <h5 class="text-center mb-3">Register</h5>
            <form method="post" action="register.php">
                <div class="mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" name="first_name" id="first_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" name="last_name" id="last_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>

                <?php if (isset($register_err)) echo '<div class="alert alert-danger">'.$register_err.'</div>'; ?>

                <div class="d-grid">
                    <button type="submit" name="submit" id="submit" class="btn btn-register text-white">Register</button>
                </div>
                <p class="text-center mt-3 mb-0">
                    <a href="login.php">Already have an account? Log in here!</a>
                </p>
            </form>
        </div>
    </div>

    <footer>
        <div class="footer">© 2025.</div>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

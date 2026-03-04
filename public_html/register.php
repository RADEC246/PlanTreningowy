<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Nieprawidłowy email";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$email, $hash]);
        echo "Rejestracja udana! <a href='login.php'>Zaloguj się</a>";
    }
}
?>

<form method="POST">
    Email: <input type="email" name="email" required><br>
    Hasło: <input type="password" name="password" required><br>
    <button type="submit">Zarejestruj</button>
</form>
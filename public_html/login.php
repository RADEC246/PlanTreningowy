<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Niepoprawny email lub hasło";
    }
}
?>

<form method="POST">
    Email: <input type="email" name="email" required><br>
    Hasło: <input type="password" name="password" required><br>
    <button type="submit">Zaloguj się</button>
</form>
<?php
session_start();
require 'config.php';

if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit;
}

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
        $error = "Niepoprawny email lub hasło";
    }
}
?>

<link rel="stylesheet" href="style.css">

<div class="container">

<h1>Aplikacja treningowa</h1>

<div class="card">

<h2>Logowanie</h2>

<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST">

Email  
<input type="email" name="email" required>

Hasło  
<input type="password" name="password" required>

<button type="submit">Zaloguj się</button>

</form>

<br>

<a href="register.php">Nie masz konta? Zarejestruj się</a>

</div>

</div>
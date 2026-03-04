<from method="POST">
Email: <input type="email" name="email" required><br>
Hasło: <input type="password" name="pasword" required><br>
<button type="sumbit">Zarejestruj</button>

<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Nieprawidłowy email.");
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, created_at) VALUES (?, ?, NOW())");
    try {
        $stmt->execute([$email, $hash]);
        echo "Rejestracja udana!";
    } catch (PDOException $e) {
        echo "Błąd: " . $e->getMessage();
    }
}
?>
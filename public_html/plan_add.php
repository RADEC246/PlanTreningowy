<form method="POST">
    Nazwa planu: <input type="text" name="name" required><br>
    <button type="submit">Dodaj plan</button>
</form>

<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    
    if ($name === '') {
        echo "Podaj nazwę planu.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO training_plans (user_id, name) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $name]);
        echo "Plan dodany!";
    }
}
?>
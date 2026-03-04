<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['plan_id'])) die("Nie podano planu.");
$plan_id = (int)$_GET['plan_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    if ($name !== '') {
        $stmt = $pdo->prepare("INSERT INTO workouts (plan_id, name) VALUES (?, ?)");
        $stmt->execute([$plan_id, $name]);
        header("Location: workout_view.php?plan_id=".$plan_id);
        exit;
    } else {
        echo "Podaj nazwę dnia treningowego.";
    }
}
?>

<form method="POST">
    Nazwa dnia treningowego: <input type="text" name="name" required><br>
    <button type="submit">Dodaj dzień</button>
</form>
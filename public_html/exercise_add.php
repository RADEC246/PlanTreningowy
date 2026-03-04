<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['workout_id'])) die("Nie podano dnia treningowego.");
$workout_id = (int)$_GET['workout_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $muscle_group = trim($_POST['muscle_group']);
    $sets = (int)$_POST['sets'];
    $reps = (int)$_POST['reps'];

    $stmt = $pdo->prepare("INSERT INTO exercises (name, muscle_group) VALUES (?, ?)");
    $stmt->execute([$name, $muscle_group]);
    $exercise_id = $pdo->lastInsertId();

    $stmt2 = $pdo->prepare("INSERT INTO workout_exercises (workout_id, exercise_id, sets, reps) VALUES (?, ?, ?, ?)");
    $stmt2->execute([$workout_id, $exercise_id, $sets, $reps]);

    header("Location: workout_view.php?plan_id=".$_GET['plan_id']);
    exit;
}
?>

<form method="POST">
    Nazwa ćwiczenia: <input type="text" name="name" required><br>
    Grupa mięśni: <input type="text" name="muscle_group" required><br>
    Serie: <input type="number" name="sets" required><br>
    Powtórzenia: <input type="number" name="reps" required><br>
    <button type="submit">Dodaj ćwiczenie</button>
</form>
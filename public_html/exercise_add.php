<?php
// Start sesji - tylko zalogowany użytkownik może dodawać ćwiczenia.
session_start();

// Dołączamy konfigurację bazy danych.
require 'config.php';

// Sprawdzamy, czy użytkownik jest zalogowany.
if (!isset($_SESSION['user_id'])) {
    // Przekierowanie do formularza logowania.
    header("Location: login.php");

    // Kończymy skrypt po redirect.
    exit;
}

// Walidujemy obecność parametru workout_id w URL.
if (!isset($_GET['workout_id'])) die("Nie podano dnia treningowego.");

// Rzutowanie na int dla bezpieczeństwa typów.
$workout_id = (int)$_GET['workout_id'];
$plan_id = (int)$_GET['plan_id'];

// Weryfikujemy, czy trening należy do zalogowanego użytkownika.
$workoutCheck = $pdo->prepare("
    SELECT tp.user_id, w.plan_id
    FROM workouts w
    JOIN training_plans tp ON w.plan_id = tp.id
    WHERE w.id = ?
");
$workoutCheck->execute([$workout_id]);
$workoutOwner = $workoutCheck->fetch();

if (!$workoutOwner || ((int)$workoutOwner['user_id'] !== (int)$_SESSION['user_id'])) {
    die("Nie masz uprawnień do tego dnia treningowego.");
}

// Dodatkowa walidacja: plan_id w GET powinien zgadzać się z tym z bazy.
if ($plan_id !== (int)$workoutOwner['plan_id']) {
    die("Niepoprawny plan.");
}

// Obsługa formularza po wysłaniu metodą POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobieramy nazwę ćwiczenia.
    $name = trim($_POST['name']);

    // Pobieramy grupę mięśniową opisaną przez użytkownika.
    $muscle_group = trim($_POST['muscle_group']);

    // Konwersja serii do liczby całkowitej.
    $sets = (int)$_POST['sets'];

    // Konwersja powtórzeń do liczby całkowitej.
    $reps = (int)$_POST['reps'];

    // Najpierw dodajemy ćwiczenie do tabeli exercises (słownik ćwiczeń).
    $stmt = $pdo->prepare("INSERT INTO exercises (name, muscle_group) VALUES (?, ?)");

    // Wykonanie INSERT z nazwą i grupą mięśni.
    $stmt->execute([$name, $muscle_group]);

    // Pobieramy ID właśnie utworzonego ćwiczenia.
    $exercise_id = $pdo->lastInsertId();

    // Potem tworzymy powiązanie ćwiczenia z dniem treningowym i parametrami (sets/reps).
    $stmt2 = $pdo->prepare("INSERT INTO workout_exercises (workout_id, exercise_id, sets, reps) VALUES (?, ?, ?, ?)");

    // Wstawiamy rekord relacji do tabeli pośredniej.
    $stmt2->execute([$workout_id, $exercise_id, $sets, $reps]);

    // Przekierowujemy do widoku planu po zapisaniu danych.
    // Uwaga edukacyjna: w tym kodzie użyty jest plan_id z GET,
    // więc wymaga on obecności parametru plan_id w URL.
    header("Location: workout_view.php?plan_id=".$plan_id);

    // Kończymy skrypt po przekierowaniu.
    exit;
}
?>
<link rel="stylesheet" href="style.css">

<!-- Formularz dodawania ćwiczenia do dnia treningowego -->
<form method="POST">
    <!-- Pole nazwy ćwiczenia -->
    Nazwa ćwiczenia: <input type="text" name="name" required><br>

    <!-- Pole grupy mięśniowej -->
    Grupa mięśni: <input type="text" name="muscle_group" required><br>

    <!-- Liczba serii -->
    Serie: <input type="number" name="sets" required><br>

    <!-- Liczba powtórzeń -->
    Powtórzenia: <input type="number" name="reps" required><br>

    <!-- Przycisk zapisujący ćwiczenie -->
    <button type="submit">Dodaj ćwiczenie</button>
</form>

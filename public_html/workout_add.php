<?php
// Start sesji, ponieważ akcja wymaga zalogowanego użytkownika.
session_start();

// Dołączenie konfiguracji bazy danych.
require 'config.php';

// Kontrola autoryzacji: tylko zalogowany użytkownik może dodawać dni treningowe.
if (!isset($_SESSION['user_id'])) {
    // Przekierowanie do logowania.
    header("Location: login.php");

    // Zatrzymanie dalszego wykonywania kodu.
    exit;
}

// Sprawdzamy, czy przekazano identyfikator planu w URL.
// Bez plan_id nie wiemy, do którego planu dodać dzień.
if (!isset($_GET['plan_id'])) die("Nie podano planu.");

// Rzutowanie na int ogranicza ryzyko podania nieoczekiwanego typu danych.
$plan_id = (int)$_GET['plan_id'];

// Obsługa zapisu po wysłaniu formularza metodą POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobieramy nazwę dnia treningowego i przycinamy białe znaki.
    $name = trim($_POST['name']);

    // Walidujemy, czy nazwa nie jest pusta.
    if ($name !== '') {
        // Przygotowane zapytanie SQL do dodania nowego dnia (workoutu).
        $stmt = $pdo->prepare("INSERT INTO workouts (plan_id, name) VALUES (?, ?)");

        // Zapisujemy rekord w tabeli workouts.
        $stmt->execute([$plan_id, $name]);

        // Po dodaniu przekierowujemy do widoku planu, żeby od razu zobaczyć efekt.
        header("Location: workout_view.php?plan_id=".$plan_id);

        // Zatrzymujemy skrypt po redirect.
        exit;
    } else {
        // Informacja dla użytkownika o brakującej nazwie.
        echo "Podaj nazwę dnia treningowego.";
    }
}
?>
<link rel="stylesheet" href="style.css">

<!-- Formularz dodania dnia treningowego -->
<form method="POST">
    <!-- Pole nazwy dnia treningowego -->
    Nazwa dnia treningowego: <input type="text" name="name" required><br>

    <!-- Przycisk zatwierdzający dodanie -->
    <button type="submit">Dodaj dzień</button>
</form>

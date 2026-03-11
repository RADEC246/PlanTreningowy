<?php
// Startujemy sesję, aby znać tożsamość zalogowanego użytkownika.
session_start();

// Dołączamy konfigurację i połączenie z bazą.
require 'config.php';

// Kontrola dostępu: bez zalogowania nie pokazujemy historii progresu.
if (!isset($_SESSION['user_id'])) {
    // Przekierowanie na stronę logowania.
    header("Location: login.php");

    // Kończymy skrypt po redirect.
    exit;
}

// Zapytanie pobiera historię progresu użytkownika wraz z nazwą ćwiczenia.
$stmt = $pdo->prepare("
    SELECT p.weight, p.reps, p.workout_date, e.name
    FROM progress p
    JOIN exercises e ON p.exercise_id = e.id
    WHERE p.user_id = ?
    ORDER BY p.workout_date DESC
");

// Podstawiamy ID użytkownika z sesji.
$stmt->execute([$_SESSION['user_id']]);

// Pobieramy wszystkie wpisy do tablicy.
$progress = $stmt->fetchAll();
?>
<link rel="stylesheet" href="style.css">

<!-- Nagłówek strony -->
<h2>Historia progresu</h2>

<!-- Link do formularza dodawania nowego wpisu -->
<a href="progress_add.php">Dodaj nowy progres</a><br><br>

<!--
    Tabela z danymi historycznymi.
    W nowoczesnych projektach zamiast atrybutów HTML typu border/cellpadding
    zwykle używa się CSS.
-->
<table border="1" cellpadding="5">
    <tr>
        <!-- Nagłówki kolumn -->
        <th>Ćwiczenie</th>
        <th>Ciężar</th>
        <th>Powtórzenia</th>
        <th>Data</th>
    </tr>

    <?php foreach ($progress as $p): ?>
        <tr>
            <!-- Nazwę ćwiczenia zabezpieczamy przed XSS -->
            <td><?php echo htmlspecialchars($p['name']); ?></td>

            <!-- Dla liczb często nie trzeba htmlspecialchars, ale można stosować spójnie -->
            <td><?php echo $p['weight']; ?> kg</td>

            <!-- Liczba powtórzeń -->
            <td><?php echo $p['reps']; ?></td>

            <!-- Data treningu -->
            <td><?php echo $p['workout_date']; ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<br>

<!-- Link do powrotu na dashboard -->
<a href="dashboard.php">Powrót do dashboard</a>

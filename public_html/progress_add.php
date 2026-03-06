<?php
// Uruchamiamy sesję, bo operacja dotyczy konkretnego zalogowanego użytkownika.
session_start();

// Dołączamy konfigurację i połączenie z bazą danych.
require 'config.php';

// Jeżeli brak sesji użytkownika, przekierowujemy na stronę logowania.
if (!isset($_SESSION['user_id'])) {
    // Redirect do loginu.
    header("Location: login.php");

    // Kończymy skrypt po redirect.
    exit;
}

// Zapytanie pobiera unikalne ćwiczenia użytkownika,
// żeby można było wybrać je z listy podczas dodawania progresu.
$stmt = $pdo->prepare("
    SELECT DISTINCT e.id, e.name
    FROM exercises e
    JOIN workout_exercises we ON we.exercise_id = e.id
    JOIN workouts w ON we.workout_id = w.id
    JOIN training_plans tp ON w.plan_id = tp.id
    WHERE tp.user_id = ?
");

// Podstawiamy ID aktualnie zalogowanego użytkownika.
$stmt->execute([$_SESSION['user_id']]);

// Pobieramy listę ćwiczeń do wyświetlenia w <select>.
$exercises = $stmt->fetchAll();

// Obsługa zapisu progresu po wysłaniu formularza POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Pobieramy ID ćwiczenia wybranego w formularzu.
    $exercise_id = (int)$_POST['exercise_id'];

    // Pobieramy ciężar i konwertujemy do liczby zmiennoprzecinkowej.
    $weight = (float)$_POST['weight'];

    // Pobieramy liczbę powtórzeń jako int.
    $reps = (int)$_POST['reps'];

    // Pobieramy datę treningu.
    // Dobra praktyka: dodatkowo walidować format daty po stronie backendu.
    $date = $_POST['workout_date'];

    // Przygotowujemy zapytanie INSERT do tabeli progress.
    $stmt = $pdo->prepare("
        INSERT INTO progress (user_id, exercise_id, weight, reps, workout_date)
        VALUES (?, ?, ?, ?, ?)
    ");

    // Wykonujemy zapytanie z przekazanymi parametrami.
    $stmt->execute([
        $_SESSION['user_id'],
        $exercise_id,
        $weight,
        $reps,
        $date
    ]);

    // Potwierdzenie zapisu dla użytkownika.
    echo "Progres zapisany!";
}
?>

<!-- Tytuł strony -->
<h2>Dodaj progres</h2>

<!-- Formularz dodawania wpisu progresu -->
<form method="POST">
    Ćwiczenie:

    <!-- Lista ćwiczeń użytkownika pobrana z bazy -->
    <select name="exercise_id" required>
        <?php foreach ($exercises as $ex): ?>
            <!--
                value zawiera ID ćwiczenia, a widoczna etykieta to nazwa.
                Nazwę zabezpieczamy przez htmlspecialchars.
            -->
            <option value="<?php echo $ex['id']; ?>">
                <?php echo htmlspecialchars($ex['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <!-- Pole na ciężar z krokiem 0.1 kg -->
    Ciężar (kg):
    <input type="number" step="0.1" name="weight" required><br><br>

    <!-- Pole na liczbę powtórzeń -->
    Powtórzenia:
    <input type="number" name="reps" required><br><br>

    <!-- Pole wyboru daty treningu -->
    Data:
    <input type="date" name="workout_date" required><br><br>

    <!-- Zatwierdzenie formularza -->
    <button type="submit">Zapisz progres</button>
</form>

<br>

<!-- Link powrotu do panelu głównego -->
<a href="dashboard.php">Powrót do dashboard</a>

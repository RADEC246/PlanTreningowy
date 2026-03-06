<?php
// Start sesji - widok jest dostępny tylko dla zalogowanych użytkowników.
session_start();

// Dołączamy konfigurację i połączenie z bazą.
require 'config.php';

// Jeżeli użytkownik nie jest zalogowany, przekierowujemy go na login.
if (!isset($_SESSION['user_id'])) {
    // Redirect do logowania.
    header("Location: login.php");

    // Koniec skryptu po wysłaniu nagłówka Location.
    exit;
}

// Sprawdzamy wymagany parametr plan_id w URL.
if (!isset($_GET['plan_id'])) die("Nie podano planu.");

// Konwersja plan_id do liczby całkowitej.
$plan_id = (int)$_GET['plan_id'];

// Pobieramy wszystkie dni treningowe (workouts) dla wskazanego planu.
$stmt = $pdo->prepare("SELECT * FROM workouts WHERE plan_id = ?");

// Wykonujemy zapytanie z plan_id.
$stmt->execute([$plan_id]);

// Odbieramy listę dni treningowych do późniejszego renderowania.
$workouts = $stmt->fetchAll();
?>

<!-- Tytuł strony -->
<h2>Dni treningowe w planie</h2>

<!-- Link nawigacyjny do dashboardu -->
<a href="dashboard.php">Powrót do planów</a><br><br>

<?php foreach ($workouts as $workout): ?>
    <!--
        Sekcja pojedynczego dnia treningowego.
        W większym projekcie style inline przenosimy do pliku CSS.
    -->
    <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
        <!-- Bezpieczne wyświetlenie nazwy dnia treningowego -->
        <strong><?php echo htmlspecialchars($workout['name']); ?></strong><br>

        <!-- Link do dodania ćwiczenia do konkretnego dnia -->
		<a href="exercise_add.php?workout_id=<?php echo $workout['id']; ?>&plan_id=<?php echo $plan_id; ?>">Dodaj ćwiczenie</a>

        <?php
        // Dla bieżącego dnia pobieramy przypisane ćwiczenia oraz parametry serii/powtórzeń.
        // JOIN łączy dane z tabeli pośredniej workout_exercises i słownika exercises.
        $stmt2 = $pdo->prepare("
            SELECT e.name, e.muscle_group, we.sets, we.reps
            FROM workout_exercises we
            JOIN exercises e ON we.exercise_id = e.id
            WHERE we.workout_id = ?
        ");

        // Podstawiamy ID konkretnego dnia treningowego.
        $stmt2->execute([$workout['id']]);

        // Pobieramy listę ćwiczeń dla tego dnia.
        $exercises = $stmt2->fetchAll();
        ?>

        <!-- Lista ćwiczeń przypisanych do dnia -->
        <ul>
        <?php foreach ($exercises as $ex): ?>
            <!--
                Wyświetlamy nazwę i grupę mięśni bezpiecznie (htmlspecialchars).
                sets/reps to liczby, które składamy do formatu np. 3x10.
            -->
            <li><?php echo htmlspecialchars($ex['name']); ?> | <?php echo htmlspecialchars($ex['muscle_group']); ?> | <?php echo $ex['sets'].'x'.$ex['reps']; ?></li>
        <?php endforeach; ?>
        </ul>
    </div>
<?php endforeach; ?>

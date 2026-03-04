<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT DISTINCT e.id, e.name
    FROM exercises e
    JOIN workout_exercises we ON we.exercise_id = e.id
    JOIN workouts w ON we.workout_id = w.id
    JOIN training_plans tp ON w.plan_id = tp.id
    WHERE tp.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$exercises = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $exercise_id = (int)$_POST['exercise_id'];
    $weight = (float)$_POST['weight'];
    $reps = (int)$_POST['reps'];
    $date = $_POST['workout_date'];

    $stmt = $pdo->prepare("
        INSERT INTO progress (user_id, exercise_id, weight, reps, workout_date)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $_SESSION['user_id'],
        $exercise_id,
        $weight,
        $reps,
        $date
    ]);

    echo "Progres zapisany!";
}
?>

<h2>Dodaj progres</h2>

<form method="POST">
    Ćwiczenie:
    <select name="exercise_id" required>
        <?php foreach ($exercises as $ex): ?>
            <option value="<?php echo $ex['id']; ?>">
                <?php echo htmlspecialchars($ex['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    Ciężar (kg):
    <input type="number" step="0.1" name="weight" required><br><br>

    Powtórzenia:
    <input type="number" name="reps" required><br><br>

    Data:
    <input type="date" name="workout_date" required><br><br>

    <button type="submit">Zapisz progres</button>
</form>

<br>
<a href="dashboard.php">Powrót do dashboard</a>
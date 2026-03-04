<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['plan_id'])) die("Nie podano planu.");
$plan_id = (int)$_GET['plan_id'];

$stmt = $pdo->prepare("SELECT * FROM workouts WHERE plan_id = ?");
$stmt->execute([$plan_id]);
$workouts = $stmt->fetchAll();
?>

<h2>Dni treningowe w planie</h2>
<a href="dashboard.php">Powrót do planów</a><br><br>

<?php foreach ($workouts as $workout): ?>
    <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
        <strong><?php echo htmlspecialchars($workout['name']); ?></strong><br>
        <a href="exercise_add.php?workout_id=<?php echo $workout['id']; ?>">Dodaj ćwiczenie</a>

        <?php
        $stmt2 = $pdo->prepare("
            SELECT e.name, e.muscle_group, we.sets, we.reps
            FROM workout_exercises we
            JOIN exercises e ON we.exercise_id = e.id
            WHERE we.workout_id = ?
        ");
        $stmt2->execute([$workout['id']]);
        $exercises = $stmt2->fetchAll();
        ?>
        <ul>
        <?php foreach ($exercises as $ex): ?>
            <li><?php echo htmlspecialchars($ex['name']); ?> | <?php echo htmlspecialchars($ex['muscle_group']); ?> | <?php echo $ex['sets'].'x'.$ex['reps']; ?></li>
        <?php endforeach; ?>
        </ul>
    </div>
<?php endforeach; ?>
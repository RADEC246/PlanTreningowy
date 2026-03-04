<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.weight, p.reps, p.workout_date, e.name
    FROM progress p
    JOIN exercises e ON p.exercise_id = e.id
    WHERE p.user_id = ?
    ORDER BY p.workout_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$progress = $stmt->fetchAll();
?>

<h2>Historia progresu</h2>

<a href="progress_add.php">Dodaj nowy progres</a><br><br>

<table border="1" cellpadding="5">
    <tr>
        <th>Ćwiczenie</th>
        <th>Ciężar</th>
        <th>Powtórzenia</th>
        <th>Data</th>
    </tr>

    <?php foreach ($progress as $p): ?>
        <tr>
            <td><?php echo htmlspecialchars($p['name']); ?></td>
            <td><?php echo $p['weight']; ?> kg</td>
            <td><?php echo $p['reps']; ?></td>
            <td><?php echo $p['workout_date']; ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<br>
<a href="dashboard.php">Powrót do dashboard</a>
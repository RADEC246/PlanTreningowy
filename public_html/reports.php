<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

$monthlyStmt = $pdo->prepare("
    SELECT DATE_FORMAT(workout_date, '%Y-%m') AS month, AVG(weight) AS avg_weight
    FROM progress
    WHERE user_id = ?
    GROUP BY month
    ORDER BY month ASC
");
$monthlyStmt->execute([$userId]);
$monthly = $monthlyStmt->fetchAll();

$exerciseStmt = $pdo->prepare("
    SELECT e.name, MAX(p.weight) AS max_weight
    FROM progress p
    JOIN exercises e ON e.id = p.exercise_id
    WHERE p.user_id = ?
    GROUP BY e.name
    ORDER BY max_weight DESC
    LIMIT 10
");
$exerciseStmt->execute([$userId]);
$bestExercises = $exerciseStmt->fetchAll();

$months = array_column($monthly, 'month');
$monthlyData = array_map(fn($row) => (float)$row['avg_weight'], $monthly);
$exerciseNames = array_column($bestExercises, 'name');
$exerciseWeights = array_map(fn($row) => (float)$row['max_weight'], $bestExercises);
?>
<link rel="stylesheet" href="style.css">
<h2>Raporty i wizualizacje</h2>
<p>Śledź rozwój siły i najlepsze rekordy.</p>

<canvas id="weightTrend" style="max-width:900px; margin-bottom:40px;"></canvas>
<canvas id="bestReps" style="max-width:900px;"></canvas>

<br>
<a href="dashboard.php">Powrót do dashboard</a>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const trendCtx = document.getElementById('weightTrend');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months, JSON_UNESCAPED_UNICODE); ?>,
        datasets: [{
            label: 'Średni ciężar (kg)',
            data: <?php echo json_encode($monthlyData); ?>,
            borderColor: '#1E88E5',
            backgroundColor: 'rgba(30,136,229,0.25)',
            fill: true,
            tension: 0.3,
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {stepSize: 5}
            }
        }
    }
});

const bestCtx = document.getElementById('bestReps');
new Chart(bestCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($exerciseNames, JSON_UNESCAPED_UNICODE); ?>,
        datasets: [{
            label: 'Największy ciężar (kg)',
            data: <?php echo json_encode($exerciseWeights); ?>,
            backgroundColor: '#43A047'
        }]
    },
    options: {
        responsive: true,
        indexAxis: 'y',
        scales: {
            x: {
                beginAtZero: true
            }
        }
    }
});
</script>

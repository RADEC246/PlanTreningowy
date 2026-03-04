<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM training_plans WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$plans = $stmt->fetchAll();
?>

<h2>Twoje plany</h2>
<a href="plan_add.php">Dodaj nowy plan</a><br><br>

<?php foreach ($plans as $plan): ?>
    <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
        <strong><?php echo htmlspecialchars($plan['name']); ?></strong><br>
        <a href="workout_add.php?plan_id=<?php echo $plan['id']; ?>">Dodaj dzień treningowy</a> |
        <a href="workout_view.php?plan_id=<?php echo $plan['id']; ?>">Zobacz dni treningowe</a>
    </div>
<?php endforeach; ?>
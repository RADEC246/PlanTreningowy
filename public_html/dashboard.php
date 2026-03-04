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

foreach ($plans as $plan) {
    echo "<p>";
    echo $plan['name'];
    echo " <a href='workout_add.php?plan_id=".$plan['id']."'>Dodaj dzień treningowy</a>";
    echo "</p>";
}
?>
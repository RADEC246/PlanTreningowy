<form method="POST">
    Nazwa dnia treningowego: <input type="text" name="name" required><br>
    <input type="hidden" name="plan_id" value="<?php echo $_GET['plan_id']; ?>">
    <button type="submit">Dodaj dzień</button>
</form>

<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $plan_id = $_POST['plan_id'];

    if ($name === '') {
        echo "Podaj nazwę dnia treningowego.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO workouts (plan_id, name) VALUES (?, ?)");
        $stmt->execute([$plan_id, $name]);
        echo "Dzień treningowy dodany!";
    }
}
?>
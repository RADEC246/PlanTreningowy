<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userStmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$currentEmail = $userStmt->fetchColumn();

if (!$currentEmail || !in_array($currentEmail, ADMIN_EMAILS, true)) {
    die("Dostęp tylko dla konta administracyjnego.");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $deleteId = (int)$_POST['delete_user'];
        if ($deleteId === $_SESSION['user_id']) {
            $message = 'Nie możesz usunąć własnego konta.';
        } else {
            $delete = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $delete->execute([$deleteId]);
            $message = 'Użytkownik i powiązane dane zostały usunięte.';
        }
    }

    if (isset($_POST['delete_plan'])) {
        $planId = (int)$_POST['delete_plan'];
        $deletePlan = $pdo->prepare("DELETE FROM training_plans WHERE id = ?");
        $deletePlan->execute([$planId]);
        $message = 'Plan treningowy został usunięty.';
    }
}

$users = $pdo->query("SELECT id, email, created_at FROM users ORDER BY created_at DESC")->fetchAll();
$planStats = $pdo->query("
    SELECT tp.id, tp.name, tp.user_id, tp.created_at, u.email
    FROM training_plans tp
    JOIN users u ON u.id = tp.user_id
    ORDER BY tp.created_at DESC
")->fetchAll();
$progressCount = $pdo->prepare("SELECT COUNT(*) FROM progress WHERE user_id = ?");

foreach ($users as &$user) {
    $progressCount->execute([$user['id']]);
    $user['progress_entries'] = $progressCount->fetchColumn();
}
unset($user);
?>
<link rel="stylesheet" href="style.css">

<h2>Panel administratora</h2>

<?php if ($message): ?>
    <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<section>
    <h3>Użytkownicy</h3>
    <table border="1" cellpadding="5">
        <tr>
            <th>Email</th>
            <th>Data utworzenia</th>
            <th>Wpisy progresu</th>
            <th>Akcja</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                <td><?php echo $user['progress_entries']; ?></td>
                <td>
                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete_user" value="<?php echo $user['id']; ?>">
                            <button type="submit" onclick="return confirm('Usunąć użytkownika?');">Usuń</button>
                        </form>
                    <?php else: ?>
                        (wyloguj się, aby usunąć)
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>

<section style="margin-top: 30px;">
    <h3>Plany treningowe</h3>
    <table border="1" cellpadding="5">
        <tr>
            <th>Nazwa</th>
            <th>Właściciel</th>
            <th>Data</th>
            <th>Akcja</th>
        </tr>
        <?php foreach ($planStats as $plan): ?>
            <tr>
                <td><?php echo htmlspecialchars($plan['name']); ?></td>
                <td><?php echo htmlspecialchars($plan['email']); ?></td>
                <td><?php echo htmlspecialchars($plan['created_at']); ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="delete_plan" value="<?php echo $plan['id']; ?>">
                        <button type="submit" onclick="return confirm('Usunąć plan?');">Usuń plan</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>

<br>
<a href="dashboard.php">Powrót do dashboard</a>

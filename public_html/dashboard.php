<?php
// Rozpoczynamy sesję, żeby odczytać identyfikator zalogowanego użytkownika.
session_start();

// Wczytujemy konfigurację i połączenie z bazą.
require 'config.php';

// Kontrola dostępu: jeśli brak user_id w sesji, użytkownik nie jest zalogowany.
if (!isset($_SESSION['user_id'])) {
    // Przekierowanie do logowania.
    header("Location: login.php");

    // Natychmiast kończymy wykonywanie skryptu po redirect.
    exit;
}

// Pobieramy plany przypisane tylko do aktualnie zalogowanego użytkownika.
// To podstawowa ochrona przed wyświetleniem cudzych danych.
$stmt = $pdo->prepare("SELECT * FROM training_plans WHERE user_id = ?");

// Podstawiamy user_id z sesji do zapytania.
$stmt->execute([$_SESSION['user_id']]);

// Pobieramy wszystkie rekordy planów do tablicy.
$plans = $stmt->fetchAll();

$emailStmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$emailStmt->execute([$_SESSION['user_id']]);
$currentEmail = $emailStmt->fetchColumn();
$isAdmin = in_array($currentEmail, ADMIN_EMAILS, true);
?>
<link rel="stylesheet" href="style.css">

<!-- Nagłówek widoku -->
<h2>Twoje plany</h2>

<!--
    Prosta nawigacja do kluczowych akcji aplikacji.
    W większej aplikacji warto zrobić osobny plik z layoutem/menu.
-->
<a href="plan_add.php">Dodaj nowy plan</a> |
<a href="progress_add.php">Dodaj progres</a> |
<a href="history.php">Historia progresu</a>
<a href="logout.php">Wyloguj</a>
<br>
<a href="import_export.php">Import / eksport</a> |
<a href="reports.php">Raporty</a>
<?php if ($isAdmin): ?>
 | <a href="admin.php">Panel admina</a>
<?php endif; ?>

<br><br>

<?php foreach ($plans as $plan): ?>
    <!--
        Jeden "kafelek" planu treningowego.
        Inline style działa, ale dobra praktyka to przeniesienie styli do CSS.
    -->
    <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
        <!--
            htmlspecialchars chroni przed XSS, gdy nazwa planu zawiera np. znaczniki HTML.
            To bardzo ważna praktyka przy wyświetlaniu danych od użytkownika.
        -->
        <strong><?php echo htmlspecialchars($plan['name']); ?></strong><br>

        <!-- Link do dodawania dnia treningowego w danym planie -->
        <a href="workout_add.php?plan_id=<?php echo $plan['id']; ?>">Dodaj dzień treningowy</a> |

        <!-- Link do podglądu dni i ćwiczeń dla danego planu -->
        <a href="workout_view.php?plan_id=<?php echo $plan['id']; ?>">Zobacz dni treningowe</a>
    </div>
<?php endforeach; ?>

<?php
// Start sesji - potrzebujemy user_id, aby przypisać nowy plan do użytkownika.
session_start();

// Dołączenie konfiguracji i połączenia PDO.
require 'config.php';

// Jeżeli użytkownik nie jest zalogowany, blokujemy dostęp do formularza dodawania planu.
if (!isset($_SESSION['user_id'])) {
    // Przekierowanie na ekran logowania.
    header("Location: login.php");

    // Zakończenie skryptu po przekierowaniu.
    exit;
}

// Obsługujemy zapis planu tylko po wysłaniu formularza metodą POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobieramy nazwę planu i usuwamy białe znaki z początku i końca.
    $name = trim($_POST['name']);

    // Prosta walidacja: nazwa nie może być pusta.
    if ($name !== '') {
        // Bezpieczne zapytanie INSERT z placeholderami.
        $stmt = $pdo->prepare("INSERT INTO training_plans (user_id, name) VALUES (?, ?)");

        // Zapisujemy plan dla zalogowanego użytkownika.
        $stmt->execute([$_SESSION['user_id'], $name]);

        // Po sukcesie wracamy na dashboard, aby zobaczyć nowo dodany plan.
        header("Location: dashboard.php");

        // Kończymy skrypt po redirect.
        exit;
    } else {
        // Komunikat, jeśli użytkownik wysłał pustą nazwę.
        echo "Podaj nazwę planu.";
    }
}
?>

<!-- Formularz dodania nowego planu -->
<form method="POST">
    <!-- Pole tekstowe z wymaganym wypełnieniem -->
    Nazwa planu: <input type="text" name="name" required><br>

    <!-- Przycisk wysyłający formularz -->
    <button type="submit">Dodaj plan</button>
</form>

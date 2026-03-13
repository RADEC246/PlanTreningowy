<?php
// Uruchamiamy sesję (spójnie z resztą aplikacji, nawet jeśli tu jeszcze
// nie zapisujemy user_id po rejestracji).
session_start();

// Dołączamy konfigurację i obiekt PDO do komunikacji z bazą.
require 'config.php';

// Obsługujemy wysłanie formularza tylko dla metody POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobieramy email i usuwamy zbędne spacje z początku/końca.
    $email = trim($_POST['email']);

    // Pobieramy hasło w postaci surowej (plain text) tylko na czas tej operacji.
    $password = $_POST['password'];

    // Walidacja formatu emaila po stronie serwera.
    // Uwaga: walidacja po stronie frontu (type=email) to za mało,
    // bo klient może ją łatwo ominąć.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Informujemy użytkownika o błędnym formacie emaila.
        echo "Nieprawidłowy email";
    } else {
        // Sprawdzamy, czy email nie jest już zarejestrowany.
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetchColumn() > 0) {
            echo "Ten email jest już zajęty.";
        } else {
            // Haszujemy hasło przed zapisem do bazy.
            // Nigdy nie zapisujemy haseł w czystym tekście.
            // PASSWORD_DEFAULT wybiera aktualnie zalecany algorytm.
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Prepared statement do bezpiecznego INSERT-a.
            $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, created_at) VALUES (?, ?, NOW())");

            // Wykonanie zapytania z danymi użytkownika.
            $stmt->execute([$email, $hash]);

            // Prosty komunikat sukcesu z linkiem do logowania.
            echo "Rejestracja udana! <a href='login.php'>Zaloguj się</a>";
        }
    }
}
?>

<!--
    Formularz rejestracji.
    Dla początkujących: backend i frontend walidują dane niezależnie,
    żeby zwiększyć wygodę i bezpieczeństwo.
-->
<form method="POST">
    <!-- Pole email z walidacją przeglądarkową -->
    Email: <input type="email" name="email" required><br>

    <!-- Pole hasła (znaki są maskowane) -->
    Hasło: <input type="password" name="password" required><br>

    <!-- Wysłanie formularza -->
    <button type="submit">Zarejestruj</button>
</form>
<link rel="stylesheet" href="style.css">	

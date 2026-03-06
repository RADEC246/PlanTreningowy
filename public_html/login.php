<?php
// Startujemy sesję na samym początku pliku.
// Dlaczego na początku? Bo PHP musi wysłać nagłówki sesji przed jakimkolwiek outputem HTML.
session_start();

// Dołączamy konfigurację oraz połączenie PDO z pliku centralnego.
require 'config.php';

// Sprawdzamy, czy formularz został wysłany metodą POST.
// Dobra praktyka: logikę zapisu/odczytu danych uruchamiać tylko dla POST,
// a zwykłe wejście na stronę (GET) traktować jako wyświetlenie formularza.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobieramy i przycinamy email z formularza (trim usuwa spacje na początku i końcu).
    $email = trim($_POST['email']);

    // Pobieramy hasło z formularza.
    // Nie używamy trim dla hasła, bo spacja może być celową częścią hasła użytkownika.
    $password = $_POST['password'];

    // Przygotowujemy zapytanie SQL.
    // Znak zapytania (?) to placeholder dla wartości - to chroni przed SQL Injection.
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");

    // Wykonujemy zapytanie i podstawiamy email do placeholdera.
    $stmt->execute([$email]);

    // Pobieramy jeden rekord użytkownika (albo false, gdy nie ma takiego emaila).
    $user = $stmt->fetch();

    // Sprawdzamy dwa warunki naraz:
    // 1) Czy użytkownik istnieje,
    // 2) Czy wpisane hasło pasuje do hasha z bazy.
    // password_verify to poprawna i bezpieczna metoda porównywania hasła z hashem.
    if ($user && password_verify($password, $user['password_hash'])) {
        // Zapisujemy ID użytkownika w sesji - od tej chwili traktujemy go jako zalogowanego.
        $_SESSION['user_id'] = $user['id'];

        // Przekierowujemy na dashboard po udanym logowaniu.
        header("Location: dashboard.php");

        // exit jest ważny: kończy skrypt od razu po redirect,
        // żeby nic więcej nie zostało przypadkowo wysłane.
        exit;
    } else {
        // Komunikat dla błędnych danych logowania.
        // Dobra praktyka: nie rozróżniać, czy błędny był email czy hasło,
        // żeby nie ułatwiać enumeracji kont.
        echo "Niepoprawny email lub hasło";
    }
}
?>

<!--
    Prosty formularz logowania.
    method="POST" powoduje wysłanie danych do tego samego pliku.
-->
<form method="POST">
    <!-- type="email" daje podstawową walidację po stronie przeglądarki -->
    Email: <input type="email" name="email" required><br>

    <!-- required oznacza, że przeglądarka nie pozwoli wysłać pustego pola -->
    Hasło: <input type="password" name="password" required><br>

    <!-- Przycisk wysyła formularz -->
    <button type="submit">Zaloguj się</button>
</form>

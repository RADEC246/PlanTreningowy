<?php
// ==============================
// Plik: config.php
// Rola: centralna konfiguracja połączenia z bazą danych.
// ==============================

// Dobra praktyka:
// Trzymamy dane konfiguracyjne w jednym miejscu, żeby łatwo je zmienić
// i nie powielać tego samego kodu w wielu plikach.

// Uwaga bezpieczeństwa (ważne):
// W tym projekcie dane dostępowe są wpisane "na sztywno" (hardcoded),
// bo to etap nauki. W projekcie produkcyjnym lepiej trzymać je w zmiennych
// środowiskowych (.env) i nigdy nie commitować haseł do repozytorium.

// Stała z adresem hosta bazy danych (serwer MySQL).
define('DB_HOST', 'blulozowski.mysql.dhosting.pl');

// Stała z nazwą bazy danych, do której łączy się aplikacja.
define('DB_NAME', 'iep3qu_radekyou');

// Stała z nazwą użytkownika bazy danych.
define('DB_USER', 'iequ9p_radekyou');

// Stała z hasłem użytkownika bazy danych.
define('DB_PASS', 'ealaegiaqu7Y');

// Blok try/catch przechwytuje wyjątki, które mogą wystąpić przy łączeniu z DB.
try {
    // Tworzymy obiekt PDO (PHP Data Objects), który daje jednolity interfejs
    // do pracy z bazą i wspiera prepared statements (ważne dla bezpieczeństwa).
    $pdo = new PDO(
        // DSN (Data Source Name) opisuje typ bazy, host, nazwę DB i charset.
        // charset=utf8 pomaga poprawnie obsługiwać polskie znaki.
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8",

        // Przekazujemy login bazy.
        DB_USER,

        // Przekazujemy hasło bazy.
        DB_PASS
    );

    // Ustawiamy tryb błędów na wyjątki.
    // Dlaczego to dobre? Błędy nie giną "po cichu" i łatwiej je debugować.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Gdy połączenie się nie uda, przerywamy działanie skryptu.
    // Uwaga: w produkcji nie powinno się pokazywać pełnych treści błędów,
    // bo mogą ujawniać wrażliwe informacje o infrastrukturze.
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}

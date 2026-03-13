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

define('DB_DEFAULT_HOST', 'blulozowski.mysql.dhosting.pl');
define('DB_DEFAULT_NAME', 'iep3qu_radekyou');
define('DB_DEFAULT_USER', 'iequ9p_radekyou');
define('DB_DEFAULT_PASS', 'ealaegiaqu7Y');

/**
 * Ładuje plik .env w prostym formacie KEY=VALUE.
 * Przygotowano to głównie do środowisk developerskich.
 */
function loadEnv(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        if (str_contains($trimmed, '=')) {
            [$key, $value] = explode('=', $trimmed, 2);
            $env[trim($key)] = trim($value);
        }
    }
    return $env;
}

$envPath = __DIR__ . '/../.env';
$env = file_exists($envPath) ? loadEnv($envPath) : [];

// Stałe konfiguracyjne z możliwością nadpisania z pliku .env.
define('DB_HOST', $env['DB_HOST'] ?? DB_DEFAULT_HOST);
define('DB_NAME', $env['DB_NAME'] ?? DB_DEFAULT_NAME);
define('DB_USER', $env['DB_USER'] ?? DB_DEFAULT_USER);
define('DB_PASS', $env['DB_PASS'] ?? DB_DEFAULT_PASS);
define('DB_CHARSET', $env['DB_CHARSET'] ?? 'utf8');
define('ADMIN_EMAILS', array_filter(array_map('trim', explode(',', $env['ADMIN_EMAILS'] ?? 'admin@example.com'))));

// Blok try/catch przechwytuje wyjątki, które mogą wystąpić przy łączeniu z DB.
try {
    // Tworzymy obiekt PDO (PHP Data Objects), który daje jednolity interfejs
    // do pracy z bazą i wspiera prepared statements (ważne dla bezpieczeństwa).
    $pdo = new PDO(
        sprintf("mysql:host=%s;dbname=%s;charset=%s", DB_HOST, DB_NAME, DB_CHARSET),
        DB_USER,
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

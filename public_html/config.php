<?php
define('DB_HOST', 'blulozowski.mysql.dhosting.pl');
define('DB_NAME', 'iep3qu_radekyou');
define('DB_USER', 'iequ9p_radekyou');
define('DB_PASS', 'ealaegiaqu7Y');

try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}

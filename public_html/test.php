<?php
// Plik diagnostyczny: prosty test połączenia z bazą danych.
// Uwaga: taki plik warto trzymać tylko lokalnie i usuwać przed publikacją.

// Dołączamy konfigurację oraz obiekt PDO.
require 'config.php';

// Wykonujemy bardzo proste zapytanie SQL, które zwraca bieżący czas serwera DB.
$stmt = $pdo->query("SELECT NOW()");

// Pobieramy pierwszy (i jedyny) wiersz wyniku.
$row = $stmt->fetch();

// Wyświetlamy komunikat potwierdzający połączenie.
// $row[0] to pierwsza kolumna z wyniku zapytania SELECT NOW().
echo "Połączenie działa. Serwer czasu: " . $row[0];
?>

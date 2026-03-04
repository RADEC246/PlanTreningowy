<?php
require 'config.php';
$stmt = $pdo->query("SELECT NOW()");
$row = $stmt->fetch();
echo "Połączenie działa. Serwer czasu: " . $row[0];
?>
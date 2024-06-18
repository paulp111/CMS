<?php
$host = '127.0.0.1';
$port = '3306';  
$dbname = 'cms_edvgraz';
$user_name = 'cms_edvgraz';
$password = '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user_name, $password);
    echo "Verbindung erfolgreich!";
} catch (PDOException $e) {
    echo "Fehler bei der Verbindung: " . $e->getMessage();
}


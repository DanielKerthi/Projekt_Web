<?php
session_start();

define('DB_HOST','localhost');
define('DB_NAME','JeremyFragrance');
define('DB_USER','root');
define('DB_PASS','password');

try {
    $pdo = new PDO(
        'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Nuk mund të lidhesh me bazën e të dhënave: " . $e->getMessage());
}

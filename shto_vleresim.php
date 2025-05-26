<?php
require 'functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'klient') {
    header("Location: login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_parfum = (int) $_POST['id_parfum'];
    $id_klient = (int) $_SESSION['user_id'];
    $yjet = (int) $_POST['yjet'];
    $koment = trim($_POST['koment']);

    if ($yjet >= 1 && $yjet <= 5 && !empty($koment)) {
        $stmt = $pdo->prepare("INSERT INTO Vleresime (id_parfum, id_klient, yjet, koment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_parfum, $id_klient, $yjet, $koment]);
    }
}

header("Location: perfume.php?id=" . $id_parfum);
exit;

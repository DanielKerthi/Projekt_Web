<?php
session_start();
require 'functions.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

global $pdo;

$emri = $_POST['emri'] ?? '';
$marka = $_POST['marka'] ?? '';
$id_kategori = $_POST['id_kategori'] ?? '';
$cmimi = $_POST['cmimi'] ?? '';
$pershkrimi = $_POST['pershkrimi'] ?? '';

$img = '';
if (!empty($_FILES['img']['name'])) {
    $targetDir = "foto/";
    $targetFile = $targetDir . basename($_FILES["img"]["name"]);
    if (move_uploaded_file($_FILES["img"]["tmp_name"], $targetFile)) {
        $img = $_FILES["img"]["name"];
    } else {
    }
}

try {
    $stmt = $pdo->prepare("INSERT INTO Produkti (emri, marka, id_kategori, cmimi, img, pershkrimi) VALUES (:emri, :marka, :id_kategori, :cmimi, :img, :pershkrimi)");
    $stmt->bindParam(':emri', $emri);
    $stmt->bindParam(':marka', $marka);
    $stmt->bindParam(':id_kategori', $id_kategori, PDO::PARAM_INT);
    $stmt->bindParam(':cmimi', $cmimi);
    $stmt->bindParam(':img', $img);
    $stmt->bindParam(':pershkrimi', $pershkrimi);
    $stmt->execute();

    header('Location: index.php');
    exit();
} catch (PDOException $e) {
    echo "Gabim në shtimin e parfumit: " . $e->getMessage();
}
?>

<?php
require 'functions.php';  
$category = $_GET['category'] ?? '';  

$query = "SELECT p.id_produkti, p.emri, p.marka, p.cmimi, k.emri AS kategoria
          FROM Produkti p
          JOIN Kategoria k ON p.id_kategori = k.id_kategori";  

if ($category) {
    $query .= " WHERE k.emri = :category";
}

$stmt = $pdo->prepare($query);

if ($category) {
    $stmt->bindParam(':category', $category, PDO::PARAM_STR);
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC); 
?>

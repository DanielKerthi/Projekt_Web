<?php
require 'functions.php'; 

if (isset($_GET['id'])) {
    $perfumeId = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM Produkti WHERE id_produkti = :id_produkti");
    $stmt->bindParam(':id_produkti', $perfumeId, PDO::PARAM_INT);
    $stmt->execute();

    $perfume = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($perfume) {
        echo json_encode($perfume); 
    } else {
        echo json_encode(["error" => "Perfume not found"]);
    }
} else {
    echo json_encode(["error" => "No ID provided"]);
}
?>

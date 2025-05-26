<?php
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['edit_id'];
    $emri = $_POST['emri'];
    $marka = $_POST['marka'];
    $id_kategori = $_POST['id_kategori'];
    $cmimi = $_POST['cmimi'];
    $pershkrimi = $_POST['pershkrimi'];

    global $pdo;
    $stmt = $pdo->prepare("SELECT img FROM Produkti WHERE id_produkti = :id");
    $stmt->execute(['id' => $id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentImg = $current['img'];

    if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $imgTmpPath = $_FILES['img']['tmp_name'];
        $imgName = basename($_FILES['img']['name']);
        $imgExt = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));

        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imgExt, $allowedExts)) {
            $newFileName = uniqid('perfume_', true) . '.' . $imgExt;
            $destPath = 'foto/' . $newFileName;

            if (move_uploaded_file($imgTmpPath, $destPath)) {
                if ($currentImg && file_exists('foto/' . $currentImg)) {
                    unlink('foto/' . $currentImg);
                }
                $imgToSave = $newFileName;
            } else {
                $imgToSave = $currentImg;
            }
        } else {
            $imgToSave = $currentImg;
        }
    } else {
        $imgToSave = $currentImg;
    }

    $updateStmt = $pdo->prepare("UPDATE Produkti SET emri = :emri, marka = :marka, id_kategori = :id_kategori, cmimi = :cmimi, pershkrimi = :pershkrimi, img = :img WHERE id_produkti = :id");
    $updateStmt->execute([
        ':emri' => $emri,
        ':marka' => $marka,
        ':id_kategori' => $id_kategori,
        ':cmimi' => $cmimi,
        ':pershkrimi' => $pershkrimi,
        ':img' => $imgToSave,
        ':id' => $id
    ]);

    header('Location: index.php');
    exit();
}
?>

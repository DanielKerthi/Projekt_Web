<?php
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['token'])) {
    $token = $_POST['token'];
    $pw    = $_POST['password'] ?? '';
    $cp    = $_POST['confirm_password'] ?? '';

    $userId = verifyToken($token);
    if (!$userId) {
        die("Token i pavlefshëm ose ka skaduar.");
    }

    if ($pw !== $cp) {
        die("Fjalëkalimet nuk përputhen.");
    }

    if (resetPassword($userId, $pw)) {
        echo "Fjalëkalimi u përditësua me sukses. <a href='login.html'>Hyr këtu</a>.";
        exit;
    } else {
        die("Gabim gjatë përditosjes së fjalëkalimit. Provoni përsëri.");
    }
}

if (empty($_GET['token'])) {
    die("Link-u i pavlefshëm.");
}

$token = $_GET['token'];
$userId = verifyToken($token);
if (!$userId) {
    die("Link-u ka skaduar ose është i pavlefshëm.");
}

?><!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <title>Rivendos Fjalëkalimin</title>
</head>
<body>
  <h1>Rivendos Fjalëkalimin</h1>
  <form method="POST">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">
    <div>
      <label>Fjalëkalim i ri:</label><br>
      <input type="password" name="password" required>
    </div>
    <br>
    <div>
      <label>Konfirmo fjalëkalimin:</label><br>
      <input type="password" name="confirm_password" required>
    </div>
    <br>
    <button type="submit">Rivendos Fjalëkalimin</button>
  </form>
</body>
</html>

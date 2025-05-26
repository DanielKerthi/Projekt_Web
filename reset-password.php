<?php
require 'functions.php';
echo"<style>
.error {
    color: red;
    font-weight: bold;
    margin-top: 20px;
}
.error a {
    color: blue;
    text-decoration: underline;
}
</style>";

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
        echo "<p class = 'error'> Fjalëkalimi u përditësua me sukses. <a href='login.html'>Hyr këtu</a> </p>.";
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
  <link rel="stylesheet" href="frontend.css">
  <title>Rivendos Fjalëkalimin</title>
</head>
<body>
  <div class="container">
    <h1 class="page-title">Rivendos Fjalëkalimin</h1>
    <form method="POST">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">
      
      <div class="form-group">
        <label for="password">Fjalëkalim i ri:</label><br>
        <input type="password" name="password" id="password" required class="form-control">
      </div>
      
      <div class="form-group">
        <label for="confirm_password">Konfirmo fjalëkalimin:</label><br>
        <input type="password" name="confirm_password" id="confirm_password" required class="form-control">
      </div>

      <button type="submit" class="btn">Rivendos Fjalëkalimin</button>
    </form>
  </div>
</body>
</html>

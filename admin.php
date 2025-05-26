<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
require 'functions.php';

// Siguro që përdoruesi është i kyçur dhe ka rol admin
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <title>Paneli i Administratorit</title>
  <link rel="stylesheet" href="frontend.css">
</head>
<body>

  <div class="container">
        <h2>Paneli i Administratorit</h2>
    <ul>
      <li><a href="register-admin.html" class="btn btn-primary">Shto Administrator të Ri</a></li>
      <li><a href="logout.php" class="btn btn-primary">Dil</a></li>
    </ul>

    <form action="index.php">
      <button type="submit" class="btn btn-primary">Shko te Faqja Kryesore</button>
    <!-- Butoni për shtimin e admin-eve të tjere -->
    <form method="GET" action="register-admin.html" class="form" style="margin-bottom: 1em;">
      <button type="submit" class="btn btn-primary">Shto admin të tjerë</button>
    </form>

    <!-- Butoni i daljes nga sistemi -->
    <form id="logout-form" method="POST" action="logout.php">
      <button type="submit" class="btn btn-secondary">Dil</button>
    </form>
  </div>
</body>
</html>
<?php
require 'functions.php';
session_start();

// Siguro që përdoruesi është i kyçur dhe ka rol admin
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html');
    exit;
}

$user = getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="styles.css">
  <script src="app.js" defer></script>
</head>
<body>
  <div class="container" id="admin-container">
    <h1 class="page-title">Mirësevini, Admin <?= htmlspecialchars($user['email']) ?></h1>
    <p>Këtu janë privilegjet e administratorit:</p>
    <ul class="admin-actions">
      <li>Menaxho përdoruesit</li>
      <li>Shiko statistikat</li>
      <li>Parametra sistemi</li>
    </ul>

    <!-- Butoni për shtimin e admin-ëve të tjerë -->
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

<?php
require 'functions.php';
session_start();

// Siguro që eshte i loguar
if (empty($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
// Nese admin, ridrejto tek `admin.php`
if ($_SESSION['role'] === 'admin') {
    header('Location: admin.php');
    exit;
}

$user = getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <title>Customer Dashboard</title>
  <link rel="stylesheet" href="styles.css">
  <script src="app.js" defer></script>
</head>
<body>
  <div class="container" id="customer-container">
    <h1 class="page-title">Mirësevini, <?= htmlspecialchars($user['email']) ?></h1>
    <p>Kjo është faqja juaj si Customer.</p>
    <ul class="customer-actions">
      <li>Shiko porositë</li>
      <li>Ndrysho profilin</li>
      <li>Ndihmë & FAQ</li>
    </ul>
    <form id="logout-form" method="POST" action="logout.php">
      <button type="submit" class="btn btn-secondary">Dil</button>
    </form>
  </div>
</body>
</html>

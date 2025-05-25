<?php
require 'functions.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

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
  <link rel="stylesheet" href="frontend.css">
  <script src="app.js" defer></script>
</head>
<body>
  <div class="container" id="customer-container">
    <h1 class="page-title">Mirësevini, <?= htmlspecialchars($user['email']) ?></h1>
    <p>Kjo është faqja juaj si Customer.</p>
    <ul class="customer-actions">
      <li><a href="#" class="btn btn-primary">Shiko porositë</a></li>
      <li><a href="#" class="btn btn-primary">Ndrysho profilin</a></li>
      <li><a href="#" class="btn btn-primary">Ndihmë & FAQ</a></li>
    </ul>
    <form id="logout-form" method="POST" action="logout.php">
      <button type="submit" class="btn btn-secondary">Dil</button>
    </form>

    <form action="index.php">
      <button type="submit" class="btn btn-primary">Shko te Faqja Kryesore</button>
    </form>
  </div>
</body>
</html>

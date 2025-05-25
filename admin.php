<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
      <li><a href="manage-products.php" class="btn btn-primary">Menaxho Produktet</a></li>
      <li><a href="manage-comments.php" class="btn btn-primary">Menaxho Komentet</a></li>
      <li><a href="logout.php" class="btn btn-primary">Dil</a></li>
    </ul>

    <form action="index.php">
      <button type="submit" class="btn btn-primary">Shko te Faqja Kryesore</button>
    </form>
  </div>
</body>
</html>
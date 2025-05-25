<?php
require 'functions.php';

$sess = session_id();

$stmt = $pdo->prepare("SELECT cmimi_cart FROM cart WHERE session_id = ?");
$stmt->execute([$sess]);
$row     = $stmt->fetch();
$total   = $row['cmimi_cart'] ?? 0.00;


$stmt2 = $pdo->prepare("SELECT SUM(sasia) FROM cart_items WHERE session_id = ?");
$stmt2->execute([$sess]);
$count = $stmt2->fetchColumn() ?: 0;
?>
<div class="cart-summary">
  <strong>Cart:</strong>
  <?= $count ?> item<?= $count !== 1 ? 's' : '' ?> —
  <?= number_format($total, 2) ?> €
  <a href="cart.php">View Cart</a>
</div>
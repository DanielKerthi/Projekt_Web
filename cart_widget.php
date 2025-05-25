<?php
require 'functions.php';

if (empty($_SESSION['user_id'])) {
  echo '<p><a href="login.html">Log in</a> to see your cart.</p>';
  return;
}
$userId = (int)$_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT rol_id FROM Perdorues WHERE id = ?");
$stmt->execute([$userId]);
if ((int)$stmt->fetchColumn() === 1) {
  echo '<p>Admins do not have carts.</p>';
  return;
}

$stmt = $pdo->prepare("SELECT id_cart, cmimi_cart FROM cart WHERE id_perdorues = ?");
$stmt->execute([$userId]);
$cart = $stmt->fetch(PDO::FETCH_ASSOC);
$cartId = $cart['id_cart'] ?? null;
$total  = $cart['cmimi_cart'] ?? 0.00;

$items = [];
if ($cartId) {
  $stmt = $pdo->prepare("
    SELECT ci.sasia, p.emri, p.cmimi
      FROM cart_items ci
      JOIN Produkti p ON ci.id_produkti = p.id_produkti
     WHERE ci.id_cart = ?
     LIMIT 5
  ");
  $stmt->execute([$cartId]);
  $items = $stmt->fetchAll();
}

?>
<div class="cart-widget">
  <?php if (empty($items)): ?>
    <p>Your cart is empty.</p>
  <?php else: ?>
    <ul class="cart-items">
      <?php foreach ($items as $it): 
        $sub = $it['sasia'] * $it['cmimi'];
      ?>
        <li>
          <?= htmlspecialchars($it['emri']) ?>
          &times; <?= $it['sasia'] ?>
          = <?= number_format($sub,2) ?> €
        </li>
      <?php endforeach; ?>
    </ul>
    <?php if (count($items) === 5): ?>
      <p>…and more</p>
    <?php endif; ?>
    <p><strong>Total: <?= number_format($total,2) ?> €</strong></p>
    <p><a href="pagesa.html">View full cart →</a></p>
  <?php endif; ?>
</div>
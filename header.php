<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user_id'])) {
    return;
}

// block admins
$userId = (int) $_SESSION['user_id'];
$stmt   = $pdo->prepare("SELECT role_id FROM Perdorues WHERE id = ?");
$stmt->execute([$userId]);
if ((int)$stmt->fetchColumn() === 1) {
    return;
}

// fetch or create cart row
$pdo->prepare("INSERT IGNORE INTO cart (id_perdorues) VALUES (?)")
    ->execute([$userId]);
$stmt = $pdo->prepare("SELECT id_cart, cmimi_cart FROM cart WHERE id_perdorues = ?");
$stmt->execute([$userId]);
$cartRow = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$cartId  = $cartRow['id_cart']   ?? null;
$total   = $cartRow['cmimi_cart'] ?? 0.00;

// count items
$count = 0;
if ($cartId) {
    $stmt2 = $pdo->prepare("SELECT SUM(sasia) FROM cart_items WHERE id_cart = ?");
    $stmt2->execute([$cartId]);
    $count = (int)$stmt2->fetchColumn();
}

// fetch up to 5 lines
$items = [];
if ($cartId) {
    $stmt3 = $pdo->prepare("
      SELECT ci.sasia, p.id_produkti, p.emri, p.cmimi
        FROM cart_items ci
        JOIN Produkti p ON ci.id_produkti = p.id_produkti
       WHERE ci.id_cart = ?
       LIMIT 5
    ");
    $stmt3->execute([$cartId]);
    $items = $stmt3->fetchAll(PDO::FETCH_ASSOC);
}

// build current-URL redirect
$redirect = htmlspecialchars($_SERVER['REQUEST_URI']);
?>

<style>
  #cart-icon {
    position: fixed; bottom: 30px; right: 20px;
    font-size: 1.5rem; cursor: pointer; z-index: 1000;
  }
  #cart-panel {
    display: none; position: fixed;
    bottom: 70px; right: 20px; width: 300px;
    max-height: 400px; overflow-y: auto;
    background: #fff; border: 1px solid #ccc;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    padding: 10px; border-radius: 4px; z-index: 1000;
  }
  .cart-widget ul { list-style: none; margin: 0; padding: 0; }
  .cart-widget li {
    padding: 4px 0; border-bottom: 1px solid #eee;
    position: relative;
  }
  .cart-widget .remove-x {
    position: absolute; right: 0; top: 0;
    padding: 2px 6px; color: red; text-decoration: none;
    font-weight: bold;
  }
</style>

<div id="cart-icon">🛒 (<?= $count ?>)</div>
<div id="cart-panel">
  <div class="cart-widget">
    <?php if ($count === 0): ?>
      <p>Shporta juaj është bosh.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($items as $it):
          $sub = $it['sasia'] * $it['cmimi'];
        ?>
          <li>
            <?= htmlspecialchars($it['emri']) ?> × <?= $it['sasia'] ?>
            = <?= number_format($sub, 2) ?> €
            <a
              href="remove_from_cart.php?
                     prodId=<?= $it['id_produkti'] ?>&
                     redirect=<?= urlencode($redirect) ?>"
              class="remove-x"
              title="Heq një nga sasia"
            >×</a>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php if (count($items) === 5): ?>
        <p>…dhe më tepër</p>
      <?php endif; ?>
      <p><strong>Totali: <?= number_format($total, 2) ?> €</strong></p>
      <p><a href="pagesa.html">Shiko shportën →</a></p>
    <?php endif; ?>
  </div>
</div>

<script>
  const icon  = document.getElementById('cart-icon'),
        panel = document.getElementById('cart-panel');

  icon.addEventListener('click', () => {
    panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
  });

  document.addEventListener('click', e => {
    if (!panel.contains(e.target) && !icon.contains(e.target)) {
      panel.style.display = 'none';
    }
  });

  if (window.location.search.includes('cart_open=1')) {
    panel.style.display = 'block';
    const url = new URL(window.location);
    url.searchParams.delete('cart_open');
    window.history.replaceState(null, '', url);
  }
</script>
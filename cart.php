<?php
require 'functions.php';

$sess = session_id();

if (isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $prodId => $newQty) {
        $prodId = intval($prodId);
        $newQty = intval($newQty);
        if ($newQty <= 0) {
            $del = $pdo->prepare("
              DELETE FROM cart_items
               WHERE session_id = ? AND id_produkti = ?
            ");
            $del->execute([$sess, $prodId]);
        } else {
            $upd = $pdo->prepare("
              UPDATE cart_items
                 SET sasia = ?
               WHERE session_id = ? AND id_produkti = ?
            ");
            $upd->execute([$newQty, $sess, $prodId]);
        }
    }
}

if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $pid = (int) $_GET['remove'];
    $pdo->prepare("
      DELETE FROM cart_items
       WHERE session_id = ? AND id_produkti = ?
    ")->execute([$sess, $pid]);
}

$sum = $pdo->prepare("
  SELECT SUM(ci.sasia * p.cmimi)
    FROM cart_items ci
    JOIN Produkti p ON ci.id_produkti = p.id_produkti
   WHERE ci.session_id = ?
");
$sum->execute([$sess]);
$total = $sum->fetchColumn() ?: 0.00;

$pdo->prepare("UPDATE cart SET cmimi_cart = ? WHERE session_id = ?")
    ->execute([$total, $sess]);

$stmt = $pdo->prepare("
  SELECT ci.id_produkti, ci.sasia, p.cmimi, p.emri
    FROM cart_items ci
    JOIN Produkti p ON ci.id_produkti = p.id_produkti
   WHERE ci.session_id = ?
");
$stmt->execute([$sess]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <title>Shporta Juaj</title>
</head>
<body>

  <?php include 'cart_summary.php'; ?>

  <h1>Shporta Juaj</h1>

  <?php if (empty($items)): ?>
    <p>Shporta është bosh.</p>
  <?php else: ?>
    <form method="post" action="cart.php">
      <table border="1" cellpadding="5">
        <tr>
          <th>Produkt</th><th>Çmimi</th>
          <th>Sasia</th><th>Nëntotali</th><th>Veprimi</th>
        </tr>
        <?php foreach ($items as $it): 
          $sub = $it['cmimi'] * $it['sasia'];
        ?>
        <tr>
          <td><?= htmlspecialchars($it['emri']) ?></td>
          <td><?= number_format($it['cmimi'],2) ?> €</td>
          <td>
            <input
              type="number"
              name="qty[<?= $it['id_produkti'] ?>]"
              value="<?= $it['sasia'] ?>"
              min="0"
            >
          </td>
          <td><?= number_format($sub,2) ?> €</td>
          <td>
            <a href="cart.php?remove=<?= $it['id_produkti'] ?>">
              Fshij
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
        <tr>
          <td colspan="3" align="right"><strong>Totali:</strong></td>
          <td colspan="2">
            <strong><?= number_format($total,2) ?> €</strong>
          </td>
        </tr>
      </table>
      <button type="submit" name="update_cart">
        Përditëso Shportën
      </button>
    </form>
  <?php endif; ?>

</body>
</html>
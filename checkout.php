<?php
require 'config.php';

if (empty($_SESSION['user_id'])) {
  header('Location: login.html?prompt='
    . urlencode('Duhet të jesh i loguar për të paguar'));
  exit;
}
$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT role_id FROM Perdorues WHERE id = ?");
$stmt->execute([$userId]);
if ((int)$stmt->fetchColumn() === 1) {
  exit('Administrators cannot checkout.');
}

$stmt = $pdo->prepare("SELECT id_cart, cmimi_cart FROM cart WHERE id_perdorues = ?");
$stmt->execute([$userId]);
$cart = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$cartId = $cart['id_cart'] ?? null;
$total  = $cart['cmimi_cart'] ?? 0.00;

if (!$cartId || $total <= 0) {
  exit('Shporta juaj është bosh.');
}

$stmt = $pdo->prepare("
  SELECT ci.id_produkti, ci.sasia, p.emri, p.cmimi
    FROM cart_items ci
    JOIN Produkti p ON ci.id_produkti = p.id_produkti
   WHERE ci.id_cart = ?
");
$stmt->execute([$cartId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <title>Checkout – Totali <?= number_format($total,2) ?> €</title>
  <script src="https://www.paypal.com/sdk/js?client-id=YOUR_CLIENT_ID&currency=EUR"></script>
</head>
<body>

  <h1>Detaji i Porosisë Tuaj</h1>
  <ul>
    <?php foreach ($items as $it): 
      $sub = $it['sasia'] * $it['cmimi'];
    ?>
      <li>
        <?= htmlspecialchars($it['emri']) ?> × <?= $it['sasia'] ?>
        = <?= number_format($sub,2) ?> €
      </li>
    <?php endforeach; ?>
  </ul>
  <p><strong>Totali: <?= number_format($total,2) ?> €</strong></p>

  <div id="paypal-button-container"></div>

  <script>
    paypal.Buttons({

      createOrder: (data, actions) => {
        return actions.order.create({
          purchase_units: [{
            amount: { value: '<?= number_format($total,2,".","") ?>' }
          }]
        });
      },

      onApprove: (data, actions) => {
        return actions.order.capture().then(details => {
          return fetch('capture_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              orderID: data.orderID
            })
          }).then(res => res.json())
            .then(json => {
              if (json.success) {
                window.location = 'order_success.php?orderID=' + encodeURIComponent(data.orderID);
              } else {
                alert('Error processing your order. Please contact support.');
              }
            });
        });
      },

      onError: err => {
        console.error(err);
        alert('Një gabim ndodhi me PayPal. Ju lutem provoni përsëri.');
      }

    }).render('#paypal-button-container');
  </script>

</body>
</html>
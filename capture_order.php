<?php
require 'config.php';
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
  echo json_encode(['success'=>false,'error'=>'Not logged in']);
  exit;
}
$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("
  SELECT c.id_cart, c.cmimi_cart
    FROM cart c
   WHERE c.id_perdorues = ?
     AND c.cmimi_cart > 0
");
$stmt->execute([$userId]);
$cart = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cart) {
  echo json_encode(['success'=>false,'error'=>'Empty cart']);
  exit;
}
$cartId = (int)$cart['id_cart'];
$total  = $cart['cmimi_cart'];

try {
  $pdo->beginTransaction();

  $ins = $pdo->prepare("
    INSERT INTO porosi (id_perdorues, paypal_order_id, status, total)
    VALUES (?, ?, 'COMPLETED', ?)
  ");
  $ins->execute([$userId, $_POST['orderID'], $total]);
  $porosiId = $pdo->lastInsertId();

  $pdo->prepare("
    INSERT INTO porosi_produkte (id_porosi, id_produkti, quantity, price)
      SELECT 
        ?,           -- new porosi ID
        ci.id_produkti,
        ci.sasia,
        p.cmimi
      FROM cart_items ci
      JOIN Produkti p ON ci.id_produkti = p.id_produkti
     WHERE ci.id_cart = ?
  ")->execute([$porosiId, $cartId]);

  $pdo->prepare("DELETE FROM cart_items     WHERE id_cart = ?")->execute([$cartId]);
  $pdo->prepare("UPDATE cart SET cmimi_cart = 0 WHERE id_cart = ?")->execute([$cartId]);

  $pdo->commit();
  echo json_encode(['success'=>true,'porosiId'=>$porosiId]);

} catch (Exception $e) {
  $pdo->rollBack();
  echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
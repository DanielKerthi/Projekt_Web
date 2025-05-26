<?php
require 'config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id_cart FROM cart WHERE id_perdorues = ?");
$stmt->execute([$userId]);
$cartId = $stmt->fetchColumn();
if (!$cartId) {
    header('Location: ' . ($_GET['redirect'] ?? 'index.php'));
    exit;
}

$prodId = isset($_GET['prodId']) && is_numeric($_GET['prodId'])
    ? (int) $_GET['prodId']
    : null;

if ($prodId) {
    $stmt = $pdo->prepare("
      SELECT sasia 
        FROM cart_items 
       WHERE id_cart = ? AND id_produkti = ?
    ");
    $stmt->execute([$cartId, $prodId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        if ($row['sasia'] > 1) {
            $pdo->prepare("
              UPDATE cart_items
                 SET sasia = sasia - 1
               WHERE id_cart = ? AND id_produkti = ?
            ")->execute([$cartId, $prodId]);
        } else {
            $pdo->prepare("
              DELETE FROM cart_items
               WHERE id_cart = ? AND id_produkti = ?
            ")->execute([$cartId, $prodId]);
        }
    }

    $sum = $pdo->prepare("
      SELECT SUM(ci.sasia * p.cmimi)
        FROM cart_items ci
        JOIN Produkti p ON ci.id_produkti = p.id_produkti
       WHERE ci.id_cart = ?
    ");
    $sum->execute([$cartId]);
    $total = $sum->fetchColumn() ?: 0.00;

    $pdo->prepare("UPDATE cart SET cmimi_cart = ? WHERE id_cart = ?")
        ->execute([$total, $cartId]);
}

$redirect = $_GET['redirect'] ?? 'index.php';
$glue = strpos($redirect, '?') !== false ? '&' : '?';
header("Location: {$redirect}{$glue}cart_open=1");
exit;
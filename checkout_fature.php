<?php
include 'config.php';

$id_perdorues = $_SESSION['user_id'] ?? null;
if (!$id_perdorues) {
    die("Ju duhet të jeni të loguar.");
}

$stmt = $pdo->prepare("SELECT email FROM Perdorues WHERE id = ?");
$stmt->execute([$id_perdorues]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$email = $user['email'] ?? '';

$sql = "SELECT ci.sasia, p.emri, p.cmimi, p.id_produkti, p.img
        FROM cart_items ci
        JOIN Produkti p ON ci.id_produkti = p.id_produkti
        JOIN cart c ON c.id_cart = ci.id_cart
        WHERE c.id_perdorues = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_perdorues]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($items)) {
    die("Shporta juaj është bosh. Nuk mund të vazhdoni me porosinë.");
}

$total = 0;
$order_items = [];

foreach ($items as $item) {
    $subtotal = $item['cmimi'] * $item['sasia'];
    $total += $subtotal;
    $order_items[] = $item + ['subtotal' => $subtotal];
}

$paypal_order_id = uniqid('paypal_');
$status = "Paguajtur";

$stmt = $pdo->prepare("INSERT INTO porosi (id_perdorues, paypal_order_id, status, total) VALUES (?, ?, ?, ?)");
$stmt->execute([$id_perdorues, $paypal_order_id, $status, $total]);
$id_porosi = $pdo->lastInsertId();

$stmt = $pdo->prepare("INSERT INTO porosi_produkte (id_porosi, id_produkti, quantity, price) VALUES (?, ?, ?, ?)");
foreach ($order_items as $item) {
    $stmt->execute([$id_porosi, $item['id_produkti'], $item['sasia'], $item['cmimi']]);
}

$pdo->prepare("DELETE FROM cart_items WHERE id_cart IN (SELECT id_cart FROM cart WHERE id_perdorues = ?)")->execute([$id_perdorues]);
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title>Fatura e Porosisë</title>
    <link rel="stylesheet" href="fatura.css">
</head>
<body>
    <div class="invoice-container">
        <h2 class="invoice-title">Fatura e Porosisë #<?= htmlspecialchars($id_porosi) ?></h2>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Emri</th>
                    <th>Çmimi</th>
                    <th>Sasia</th>
                    <th>Nëntotali</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><img src="foto/<?= htmlspecialchars($item['img']) ?>" width="80" /></td>
                        <td><?= htmlspecialchars($item['emri']) ?></td>
                        <td><?= number_format($item['cmimi'], 2) ?> €</td>
                        <td><?= $item['sasia'] ?></td>
                        <td><?= number_format($item['subtotal'], 2) ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3 class="invoice-total">Totali: <?= number_format($total, 2) ?> €</h3>

        <form method="GET" action="fatura.php" class="invoice-form">
            <input type="hidden" name="id_porosi" value="<?= htmlspecialchars($id_porosi) ?>">
            <button type="submit" class="download-btn">📄 Shkarko Faturën (PDF)</button>
        </form>
    </div>
</body>
</html>

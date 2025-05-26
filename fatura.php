<?php
session_start();
include 'config.php';
require_once('TCPDF-6.9.4/tcpdf.php');

$id_porosi = $_GET['id_porosi'] ?? null;
$id_perdorues = $_SESSION['user_id'] ?? null;

if (!$id_porosi || !$id_perdorues) {
    die("Parametra të paqëndrueshëm ose nuk jeni të loguar.");
}

// Kontrollo nëse kjo porosi i përket përdoruesit
$stmt = $pdo->prepare("SELECT * FROM porosi WHERE id_porosi = ? AND id_perdorues = ?");
$stmt->execute([$id_porosi, $id_perdorues]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Porosia nuk u gjet ose nuk i përket përdoruesit.");
}

// Merr produktet e porosisë
$stmt = $pdo->prepare("
    SELECT pp.quantity, p.emri, p.cmimi, p.img 
    FROM porosi_produkte pp 
    JOIN Produkti p ON pp.id_produkti = p.id_produkti 
    WHERE pp.id_porosi = ?
");
$stmt->execute([$id_porosi]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Krijo PDF me TCPDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Jeremy Fragrance');
$pdf->SetTitle('Fatura #' . $id_porosi);
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

// Lexo CSS-in nga file
$css = file_get_contents(__DIR__ . '/fatura_print.css');

// Fillimi i HTML me CSS
$html = '<style>' . $css . '</style>';
$html .= '<div>
    <h1>Fatura e Porosisë #' . $id_porosi . '</h1>
    <table>
        <tr>
            <th>Foto</th>
            <th>Emri</th>
            <th>Çmimi</th>
            <th>Sasia</th>
            <th>Nëntotali</th>
        </tr>';

$total = 0;
foreach ($order_items as $item) {
    $subtotal = $item['cmimi'] * $item['quantity'];
    $total += $subtotal;

    $imgFile = __DIR__ . '/foto/' . $item['img'];
    $imgData = '';
    if (file_exists($imgFile)) {
        $type = pathinfo($imgFile, PATHINFO_EXTENSION);
        $data = file_get_contents($imgFile);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        $imgData = "<img src=\"$base64\" />";
    }

    $html .= '<tr>
        <td class="img-cell">' . $imgData . '</td>
        <td>' . htmlspecialchars($item['emri']) . '</td>
        <td>' . number_format($item['cmimi'], 2) . ' €</td>
        <td>' . $item['quantity'] . '</td>
        <td>' . number_format($subtotal, 2) . ' €</td>
    </tr>';
}

$html .= '</table>';
$html .= '<div class="total">Totali: ' . number_format($total, 2) . ' €</div>';
$html .= '</div>';

// Shkruaj HTML në PDF
$pdf->writeHTML($html, true, false, true, false, '');

ob_end_clean(); // Pastro buffer përpara outputit të PDF
$pdf->Output('JeremyFragrance#' . $id_porosi . '.pdf', 'D');
exit;

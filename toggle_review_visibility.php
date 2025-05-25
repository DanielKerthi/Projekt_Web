<?php
require 'functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo 'Ndalohet';
    exit;
}

$id = $_POST['id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$id || !in_array($action, ['hide', 'show'])) {
    http_response_code(400);
    echo 'Kërkesë e pavlefshme';
    exit;
}

$newStatus = ($action === 'hide') ? 1 : 0;

$stmt = $pdo->prepare("UPDATE Vleresime SET fshehur = ? WHERE id_vleresimi = ?");
if ($stmt->execute([$newStatus, $id])) {
    echo 'ok';
} else {
    echo 'error';
}

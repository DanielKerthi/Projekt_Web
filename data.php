<?php
require 'functions.php';

header('Content-Type: application/json');

$categories = getCategories();
$parfumes = getParfumes();

echo json_encode([
    'categories' => $categories,
    'parfumes' => $parfumes
]);

<?php
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username']);
    $e = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $p = $_POST['password'];
    $cp = $_POST['confirm_password'];

    if (!$e) {
        die("Email i pavlefshëm.");
    }
    if ($p !== $cp) {
        die("Fjalëkalimet nuk përputhen.");
    }
    if (registerUser($u, $e, $p)) {
        header('Location: login.html');
        exit;
    } else {
        die("Gabim gjatë regjistrimit. Provoni përsëri.");
    }
}

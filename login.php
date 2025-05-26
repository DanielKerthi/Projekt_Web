<?php
require 'functions.php';
session_start();
echo"<style>
.error {
    color: red;
    font-weight: bold;
    margin-top: 20px;
}
.error a {
    color: blue;
    text-decoration: underline;
}
</style>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['identifier']);
    $password = $_POST['password'];

    if (!isPasswordValid($password)) {
        echo "<p class = 'error'> Fjalëkalimi duhet të ketë të paktën 8 karaktere dhe një numër. <a href='login.html'>Provo përsëri</a></p>.";
        exit;
    }

    if ($user = loginUser($email, $password)) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: admin.php');
        } else {
            header('Location: customer.php');
        }
        exit;
    } else {
        echo "<p class='error'> Kredencialet janë të pasakta. <a href='login.html'>Provo përsëri</a></p>.";
    }
}

<?php
require_once 'config.php';
date_default_timezone_set('Europe/Tirane');
/**
 * Regjistron nje user të ri (si customer me role_id=2)
 */
function registerUser($email, $password) {
    global $pdo;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO perdorues (email, password, role_id)
        VALUES (:email, :password, 2)
    ");
    return $stmt->execute([
        ':email'    => $email,
        ':password' => $hash,
    ]);
}

/**
 * Ben login: kthen array(user) perfshire emrin e rolit, ose false
 */
function loginUser($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.id, u.email, u.password, r.name AS role
        FROM perdorues u
        JOIN Rolet r ON u.role_id = r.id
        WHERE u.email = :email
    ");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

/**
 * Dergon email per rikuperim
 */
function sendPasswordReset($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM perdorues WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return false;
    }

    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + 3600);

    $upd = $pdo->prepare("
        UPDATE perdorues
        SET reset_token = :token, reset_expires = :expires
        WHERE id = :id
    ");
    $upd->execute([
        ':token'   => $token,
        ':expires' => $expires,
        ':id'      => $row['id'],
    ]);

    $link    = "http://localhost/Projekt_Web-main/reset-password.php?token=$token";
    $subject = "Rikuperim Fjalëkalimi";
    $message = "Kliko këtu për të rivendosur fjalëkalimin: $link";
    return mail($email, $subject, $message);
}

/**
 * Verifikon token dhe kthen ID e user-it
 */
function verifyToken($token) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT id FROM perdorues
        WHERE reset_token = :token
          AND reset_expires > NOW()
    ");
    $stmt->execute([':token' => $token]);
    return $stmt->fetchColumn();
}

/**
 * Rivendos fjalekalimin
 */
function resetPassword($userId, $newPassword) {
    global $pdo;
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        UPDATE perdorues
        SET password      = :password,
            reset_token   = NULL,
            reset_expires = NULL
        WHERE id = :id
    ");
    return $stmt->execute([
        ':password' => $hash,
        ':id'       => $userId,
    ]);
}

/**
 * Kthen të dhenat e user
 */
function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.id, u.email, r.name AS role
        FROM perdorues u
        JOIN Rolet r ON u.role_id = r.id
        WHERE u.id = :id
    ");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

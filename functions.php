<?php
require_once 'config.php';
date_default_timezone_set('Europe/Tirane');
/**
 * Funksion per te kontrolluar numrin e karaktereve dhe a permban numra fjalekalimi
 */
function isPasswordValid($password) {
    return strlen($password) >= 8 && preg_match('/\d/', $password);
}

/**
 * Regjistron nje user të ri (si customer me role_id=2)
 */
function registerUser($email, $password) {
    global $pdo;

    if (!isPasswordValid($password)) {
        return false; 
    }

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

    $link    = "http://localhost/test/Projekt_Web-main/reset-password.php?token=$token";
    $subject = "Rikuperim Fjalëkalimi";
    $message = "Për të rivendosur fjalëkalimin <a href= '$link'> Kliko këtu </a>";
    $headers = "Content-type: text/html; charset=UTF-8";
    return mail($email, $subject, $message, $headers);
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

    if (!isPasswordValid($newPassword)) {
        return false;
    }

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
  * Regjistron nje Admin (role_id = 1)
  */
 function registerAdmin($email, $password) {
     global $pdo;
     $hash = password_hash($password, PASSWORD_DEFAULT);
     $stmt = $pdo->prepare("
         INSERT INTO perdorues (email, password, role_id)
         VALUES (:email, :password, 1)
     ");
     return $stmt->execute([
         ':email'    => $email,
         ':password' => $hash,
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
function getCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM Kategoria");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getParfumes() {
    global $pdo;
    $sql = "SELECT * FROM Produkti";
    $params = [];

    if (!empty($_GET['category'])) {
        $sql .= " WHERE id_kategori = ?";
        $params[] = (int)$_GET['category'];
    } elseif (!empty($_GET['query'])) {
        $sql .= " WHERE emri LIKE ? OR marka LIKE ?";
        $searchTerm = '%' . $_GET['query'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getPerfumeById($id) {
    global $pdo;  
    $stmt = $pdo->prepare("SELECT * FROM Produkti WHERE id_produkti = :id_produkti");
    $stmt->bindParam(':id_produkti', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function getCategoryName($categoryId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT emri FROM Kategoria WHERE id_kategori = ?");
    $stmt->execute([$categoryId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['emri'] : 'e panjohur';
}
/////////////////////////////////////////////////////////////////////


if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    if (isset($_GET['add_to_cart'])) {
        header('Location: login.html?prompt='
            . urlencode('Duhet të jesh i loguar që të shtosh në shportë'));
        exit;
    }
    return;
}

$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id_cart FROM cart WHERE id_perdorues = ?");
$stmt->execute([$userId]);
$cartId = $stmt->fetchColumn();

if (!$cartId) {
    $pdo
      ->prepare("INSERT INTO cart (id_perdorues) VALUES (?)")
      ->execute([$userId]);
    $cartId = $pdo->lastInsertId();
}

if (isset($_GET['add_to_cart']) && is_numeric($_GET['add_to_cart'])) {
    $prodId = (int) $_GET['add_to_cart'];

    $roleStmt = $pdo->prepare("SELECT role_id FROM Perdorues WHERE id = ?");
    $roleStmt->execute([$userId]);
    $roleId = (int) $roleStmt->fetchColumn();
    if ($roleId === 1) {
        header("Location: perfume.php?id={$prodId}");
        exit;
    }

    $sel = $pdo->prepare("
      SELECT sasia
        FROM cart_items
       WHERE id_cart = ?
         AND id_produkti = ?
    ");
    $sel->execute([$cartId, $prodId]);
    $row = $sel->fetch();

    if ($row) {
        $newQty = $row['sasia'] + 1;
        $pdo->prepare("
          UPDATE cart_items
             SET sasia = ?
           WHERE id_cart = ?
             AND id_produkti = ?
        ")->execute([$newQty, $cartId, $prodId]);
    } else {
        $pdo->prepare("
          INSERT INTO cart_items (id_cart, id_produkti, sasia)
               VALUES (?, ?, 1)
        ")->execute([$cartId, $prodId]);
    }

    $sum = $pdo->prepare("
      SELECT SUM(ci.sasia * p.cmimi)
        FROM cart_items ci
        JOIN Produkti p ON ci.id_produkti = p.id_produkti
       WHERE ci.id_cart = ?
    ");
    $sum->execute([$cartId]);
    $total = $sum->fetchColumn() ?: 0.00;

    $pdo
      ->prepare("UPDATE cart SET cmimi_cart = ? WHERE id_cart = ?")
      ->execute([$total, $cartId]);

    $base = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: {$base}?id={$prodId}");
    exit;
}

/////////////////////////

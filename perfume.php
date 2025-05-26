<?php
require 'functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Parfum i pavlefshëm.";
    exit;
}

$perfumeId = (int) $_GET['id'];
$perfume = getPerfumeById($perfumeId);

if (!$perfume) {
    echo "Parfumi nuk u gjet.";
    exit;
}

$stmt = $pdo->prepare("
    SELECT v.id_vleresimi, v.yjet, v.koment, v.data_dhene, v.fshehur, u.email AS emri_perdoruesit
    FROM Vleresime v
    JOIN perdorues u ON v.id_klient = u.id
    WHERE v.id_parfum = ?
    ORDER BY v.data_dhene DESC
");
$stmt->execute([$perfumeId]);
$vleresimet = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($perfume['emri']) ?> - Detajet</title>
  <link rel="stylesheet" href="perfume.css">
  <style>
    .review[style*="opacity: 0.5;"] {
      background-color: #eee;
      padding: 10px;
      border-radius: 5px;
      position: relative;
    }
    .toggle-hide {
      cursor: pointer;
      margin-left: 10px;
      font-weight: bold;
      color: red;
      text-decoration: none;
      position: absolute;
      right: 10px;
      top: 10px;
    }
    .review {
      margin-bottom: 15px;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      position: relative;
    }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>
  <div class="container">
  <div class="top-bar">
    <a href="index.php" class="logo">Jeremy Fragrance</a>
    <div class="auth-links">
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?= $_SESSION['role'] === 'admin' ? 'admin.php' : 'customer.php' ?>">Profili Im</a>
        <a href="logout.php">Dil</a>
      <?php else: ?>
        <a href="login.html">Kyçu</a>
        <a href="register.html">Regjistrohu</a>
      <?php endif; ?>
    </div>
  </div>


    <a href="index.php" class="btn btn-secondary">← Kthehu</a>
   <div class="product-detail">
  <img src="foto/<?= htmlspecialchars($perfume['img']) ?>" alt="<?= htmlspecialchars($perfume['emri']) ?>">

  <div class="product-info">
    <h1><?= htmlspecialchars($perfume['emri']) ?></h1>
    <p><strong>Marka:</strong> <?= htmlspecialchars($perfume['marka']) ?></p>
    <p><strong>Çmimi:</strong> <?= number_format($perfume['cmimi'], 2) ?> €</p>
    <p><strong>Kategori:</strong> <?= htmlspecialchars(getCategoryName($perfume['id_kategori'])) ?></p>
    <p id="pershkrimi"><?= nl2br(htmlspecialchars($perfume['pershkrimi'])) ?></p>

    <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
      <a href="perfume.php?id=<?= $perfumeId ?>&add_to_cart=<?= $perfumeId ?>">
        🛒 Shto te Shporta
      </a>
    <?php endif; ?>
  </div>

</div>

    <div class="reviews">
      <h2>Vlerëso këtë parfum</h2>

      <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'klient'): ?>
        <div class="add-review">
          <form action="shto_vleresim.php" method="POST">
            <input type="hidden" name="id_parfum" value="<?= $perfumeId ?>">

            <label for="yjet">Yjet (1-5):</label>
            <select name="yjet" id="yjet" required>
              <option value="">Zgjidh</option>
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?> yje</option>
              <?php endfor; ?>
            </select>

            <label for="koment">Komenti:</label>
            <textarea name="koment" id="koment" rows="3" required></textarea>

            <button type="submit">Dërgo Vlerësimin</button>
          </form>
        </div>
      <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <p>Adminët nuk mund të lënë komente.</p>
      <?php else: ?>
        <p><a href="login.html">Kyçu</a> për të lënë një vlerësim.</p>
      <?php endif; ?>

      <hr>

      <h3>Vlerësimet e përdoruesve</h3>
      <?php if (empty($vleresimet)): ?>
        <p>Ende nuk ka vlerësime për këtë parfum.</p>
      <?php else: ?>
        <?php
          $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        ?>
        <?php foreach ($vleresimet as $v): ?>
          <?php
            if (!$isAdmin && $v['fshehur']) continue;
          ?>
          <div class="review" id="review-<?= $v['id_vleresimi'] ?>" style="<?= $v['fshehur'] ? 'opacity: 0.5;' : '' ?>">
            <strong><?= htmlspecialchars($v['emri_perdoruesit']) ?></strong>
            <span><?= str_repeat("⭐", (int)$v['yjet']) ?></span>
            <p><?= nl2br(htmlspecialchars($v['koment'])) ?></p>
            <small><?= htmlspecialchars($v['data_dhene']) ?></small>

            <?php if ($isAdmin): ?>
              <a href="#" class="toggle-hide" data-id="<?= $v['id_vleresimi'] ?>" data-action="<?= $v['fshehur'] ? 'show' : 'hide' ?>">
                <?= $v['fshehur'] ? '🔄 Shfaq' : '❌ Fshih' ?>
              </a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <script>
    document.querySelectorAll('.toggle-hide').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        const id = this.dataset.id;
        const action = this.dataset.action;

        fetch('toggle_review_visibility.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `id=${id}&action=${action}`
        })
        .then(response => response.text())
        .then(response => {
          if (response === 'ok') {
            location.reload();
          } else {
            alert('Gabim gjatë fshehjes/shfaqjes së vlerësimit.');
          }
        });
      });
    });
  </script>

</body>
</html>

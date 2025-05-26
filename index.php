<?php
require 'functions.php';

$categories = getCategories();
$parfumes = getParfumes();
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// ✅ Allow only valid sort fields to prevent errors
$allowedSortFields = ['emri', 'cmimi'];
if (isset($_GET['sort_by']) && in_array($_GET['sort_by'], $allowedSortFields)) {
    $sortBy = $_GET['sort_by'];
    $order = $_GET['order'] ?? 'asc';

    usort($parfumes, function ($a, $b) use ($sortBy, $order) {
        $valA = $a[$sortBy];
        $valB = $b[$sortBy];

        // Make string comparison case-insensitive
        if (is_string($valA)) $valA = strtolower($valA);
        if (is_string($valB)) $valB = strtolower($valB);

        if ($valA == $valB) return 0;
        return ($order === 'asc') ? ($valA < $valB ? -1 : 1) : ($valA > $valB ? -1 : 1);
    });
}

if (isset($_GET['delete_id'])) {
    $perfumeId = $_GET['delete_id'];
    deletePerfume($perfumeId);
    header("Location: index.php");
    exit();
}

function deletePerfume($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM Produkti WHERE id_produkti = :id_produkti");
    $stmt->bindParam(':id_produkti', $id, PDO::PARAM_INT);
    $stmt->execute();
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <title>Jeremy Fragrance - Dyqani</title>
  <link rel="stylesheet" href="home.css">
  <script>
    function toggleAddForm() {
      var form = document.getElementById('add-perfume-form');
      var button = document.getElementById('toggle-add-form-button');
      if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
        button.textContent = 'Fshih Formën';
      } else {
        form.style.display = 'none';
        button.textContent = 'Shto Parfum';
      }
    }

    function toggleEditForm() {
      var form = document.getElementById('edit-perfume-form');
      var button = document.getElementById('toggle-edit-form-button');
      if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
        button.textContent = 'Fshih Formën';
      } else {
        form.style.display = 'none';
        button.textContent = 'Edito Parfum';
      }
    }

    function autofillEditForm() {
      const parfumes = <?php echo json_encode($parfumes); ?>;
      const select = document.getElementById('select_perfume');
      const selectedId = select.value;
      const emriInput = document.getElementById('emri_edit');
      const markaInput = document.getElementById('marka_edit');
      const kategoriSelect = document.getElementById('id_kategori_edit');
      const cmimiInput = document.getElementById('cmimi_edit');
      const pershkrimiTextarea = document.getElementById('pershkrimi_edit');

      if (!selectedId) {
        emriInput.value = '';
        markaInput.value = '';
        kategoriSelect.value = '';
        cmimiInput.value = '';
        pershkrimiTextarea.value = '';
        return;
      }

      const selectedPerfume = parfumes.find(p => p.id_produkti == selectedId);
      if (selectedPerfume) {
        emriInput.value = selectedPerfume.emri || '';
        markaInput.value = selectedPerfume.marka || '';
        kategoriSelect.value = selectedPerfume.id_kategori || '';
        cmimiInput.value = selectedPerfume.cmimi || '';
        pershkrimiTextarea.value = selectedPerfume.pershkrimi || '';
      }
    }

    function handleSortChange() {
      document.querySelector('.search-bar').submit();
    }

    function toggleOrder() {
      const orderInput = document.getElementById('order');
      orderInput.value = orderInput.value === 'asc' ? 'desc' : 'asc';
      document.querySelector('.search-bar').submit();
    }
  </script>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
  <div class="top-bar">
    <div class="logo"><a href='index.php'>Jeremy Fragrance</a></div>
    <div class="auth-links">
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?= $_SESSION['role'] === 'admin' ? 'admin.php' : 'customer.php' ?>">Profili Im</a>
        <a href="logout.php">Dil</a>
      <?php else: ?>
        <a href="login.html">Hyr</a>
        <a href="register.html">Regjistrohu</a>
      <?php endif; ?>
    </div>
  </div>

  <nav class="navbar">
    <form method="GET" class="search-bar">
      <input type="text" name="query" placeholder="Kërko parfume..." value="<?= htmlspecialchars($_GET['query'] ?? '') ?>">
      <button type="submit" class="btn btn-primary">Kërko</button>

      <select name="sort_by" id="sort_by" onchange="handleSortChange()">
        <option value="">Rendit sipas</option>
        <option value="emri" <?= isset($_GET['sort_by']) && $_GET['sort_by'] == 'emri' ? 'selected' : '' ?>>Emri</option>
        <option value="cmimi" <?= isset($_GET['sort_by']) && $_GET['sort_by'] == 'cmimi' ? 'selected' : '' ?>>Çmimi</option>
      </select>
      <input type="hidden" name="order" id="order" value="<?= isset($_GET['order']) && $_GET['order'] === 'desc' ? 'desc' : 'asc' ?>">
      <span id="arrow" style="cursor:pointer;" onclick="toggleOrder()">
        <?= (isset($_GET['order']) && $_GET['order'] === 'desc') ? '▼' : '▲' ?>
      </span>
    </form>

    <div class="category-links">
      <?php foreach ($categories as $cat): ?>
        <a href="?category=<?= $cat['id_kategori'] ?>"><?= htmlspecialchars($cat['emri']) ?></a>
      <?php endforeach; ?>
    </div>
  </nav>

  <?php if ($isAdmin): ?>
    <div class="admin-controls">
      <button id="toggle-add-form-button" class="btn btn-primary" onclick="toggleAddForm()">Shto Parfum</button>

      <div id="add-perfume-form" class="form-wrapper" style="display: none;">
        <h2>Shto Parfum të Ri</h2>
        <form method="POST" action="add-perfume.php" enctype="multipart/form-data" class="form">
          <div class="form-group">
            <label for="emri">Emri i Parfumit:</label>
            <input type="text" name="emri" id="emri" required>
          </div>
          <div class="form-group">
            <label for="marka">Marka:</label>
            <input type="text" name="marka" id="marka" required>
          </div>
          <div class="form-group">
            <label for="kategori">Kategoria:</label>
            <select name="id_kategori" id="kategori" required>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id_kategori'] ?>"><?= htmlspecialchars($cat['emri']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="cmimi">Çmimi (€):</label>
            <input type="number" name="cmimi" id="cmimi" step="0.01" required>
          </div>
          <div class="form-group">
            <label for="pershkrimi">Përshkrimi:</label>
            <textarea name="pershkrimi" id="pershkrimi" rows="4" required></textarea>
          </div>
          <div class="form-group">
            <label for="img">Përzgjedh imazhin:</label>
            <input type="file" name="img" id="img" accept="image/*">
          </div>
          <button type="submit" class="btn btn-primary">Ruaj Parfumin</button>
        </form>
      </div>

      <button id="toggle-edit-form-button" class="btn btn-primary" onclick="toggleEditForm()">Edito Parfum</button>

      <div id="edit-perfume-form" class="form-wrapper" style="display: none;">
        <h2>Edito Parfum</h2>
        <form method="POST" action="edit-perfume.php" enctype="multipart/form-data" class="form">
          <div class="form-group">
            <label for="select_perfume">Përzgjedh Parfum:</label>
            <select name="edit_id" id="select_perfume" onchange="autofillEditForm()">
              <option value="">Përzgjedh një parfum për të edituar</option>
              <?php foreach ($parfumes as $perfume): ?>
                <option value="<?= $perfume['id_produkti'] ?>"><?= htmlspecialchars($perfume['emri']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="emri_edit">Emri i Parfumit:</label>
            <input type="text" name="emri" id="emri_edit" required>
          </div>
          <div class="form-group">
            <label for="marka_edit">Marka:</label>
            <input type="text" name="marka" id="marka_edit" required>
          </div>
          <div class="form-group">
            <label for="id_kategori_edit">Kategoria:</label>
            <select name="id_kategori" id="id_kategori_edit" required>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id_kategori'] ?>"><?= htmlspecialchars($cat['emri']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="cmimi_edit">Çmimi (€):</label>
            <input type="number" name="cmimi" id="cmimi_edit" step="0.01" required>
          </div>
          <div class="form-group">
            <label for="pershkrimi_edit">Përshkrimi:</label>
            <textarea name="pershkrimi" id="pershkrimi_edit" rows="4"></textarea>
          </div>
          <div class="form-group">
            <label for="img_edit">Përzgjedh imazhin:</label>
            <input type="file" name="img" id="img_edit" accept="image/*">
          </div>
          <button type="submit" class="btn btn-primary">Ruaj Ndryshimet</button>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <main class="product-list">
    <?php foreach ($parfumes as $p): ?>
      <div class="product-card">
        <a href="perfume.php?id=<?= $p['id_produkti'] ?>">
          <img src="foto/<?= htmlspecialchars(empty($p['img']) ? 'placeholder.jpg' : $p['img']) ?>" alt="<?= htmlspecialchars($p['emri']) ?>">
          <h3><?= htmlspecialchars($p['emri']) ?></h3>
        </a>
        <p><?= htmlspecialchars($p['marka']) ?></p>
        <p><?= number_format($p['cmimi'], 2) ?> €</p>
        <?php if ($isAdmin): ?>
          <a href="?delete_id=<?= $p['id_produkti'] ?>" class="delete-link" onclick="return confirm('A jeni të sigurt që doni të fshini këtë parfum?');">X</a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </main>
</div>
</body>
</html>

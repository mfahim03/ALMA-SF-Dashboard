<?php
include 'db.php';
include 'auth_check.php';

session_start();
$isAdmin = $_SESSION['is_admin'] ?? false;
if (!$isAdmin) {
    header('Location: index.php');
    exit;
}

// -------------------- HANDLE CATEGORY UPDATES --------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_category'])) {
    $typeID = $_POST['type_id'];
    $icon = $_POST['icon'];
    $description = $_POST['description'];
    $bgImage = $_POST['bg_image'];
    
    // Check if category customization exists
    $sqlCheck = "SELECT COUNT(*) as count FROM CategoryCustomization WHERE type_id = ?";
    $stmtCheck = sqlsrv_query($conn, $sqlCheck, [$typeID]);
    $exists = sqlsrv_fetch_array($stmtCheck, SQLSRV_FETCH_ASSOC)['count'] > 0;
    
    if ($exists) {
        $sqlUpdate = "UPDATE CategoryCustomization SET icon=?, description=?, bg_image=? WHERE type_id=?";
        $params = [$icon, $description, $bgImage, $typeID];
    } else {
        $sqlUpdate = "INSERT INTO CategoryCustomization (type_id, icon, description, bg_image) VALUES (?, ?, ?, ?)";
        $params = [$typeID, $icon, $description, $bgImage];
    }
    
    sqlsrv_query($conn, $sqlUpdate, $params);
    header('Location: admin.php?updated=1');
    exit;
}

// -------------------- FETCH TYPES --------------------
$typeMap = [];
$sqlTypes = "SELECT TypeID, Type FROM Type";
$stmtTypes = sqlsrv_query($conn, $sqlTypes);
if ($stmtTypes) {
    while ($row = sqlsrv_fetch_array($stmtTypes, SQLSRV_FETCH_ASSOC)) {
        $typeMap[$row['TypeID']] = $row['Type'];
    }
}

// -------------------- FETCH CATEGORY CUSTOMIZATIONS --------------------
$categoryCustom = [];
$sqlCustom = "SELECT type_id, icon, description, bg_image FROM CategoryCustomization";
$stmtCustom = sqlsrv_query($conn, $sqlCustom);
if ($stmtCustom) {
    while ($row = sqlsrv_fetch_array($stmtCustom, SQLSRV_FETCH_ASSOC)) {
        $categoryCustom[$row['type_id']] = $row;
    }
}

function fetchProjects($conn, $typeMap) {
    $sql = "SELECT id, name, image, status, type AS TypeID, ip FROM Projects";
    $stmt = sqlsrv_query($conn, $sql);
    $projects = [];
    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $row['typeName'] = $typeMap[$row['TypeID']] ?? 'Unknown';
            $projects[] = $row;
        }
    }
    return $projects;
}

$allProjectsList = fetchProjects($conn, $typeMap);

// -------------------- COUNT PROJECTS BY TYPE --------------------
$projectCounts = [];
foreach ($typeMap as $typeID => $typeName) {
    $projectCounts[$typeID] = count(array_filter($allProjectsList, fn($p) => $p['TypeID'] == $typeID));
}

$SQLtitle = "ALMA SF Dashboard";

// Get list of background images
$bgImages = [];
$imgDir = 'img/';
if (is_dir($imgDir)) {
    $files = scandir($imgDir);
    foreach ($files as $file) {
        if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
            $bgImages[] = $file;
        }
    }
}
sqlsrv_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="img/png" href="/img/logo2.png">
    <title><?= htmlspecialchars($SQLtitle) ?></title>
    <link rel="icon" type="image/png" href="img/alps.png">
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="css/admin.css" />
    <link rel="stylesheet" href="css/chatbox.css" />
    <link rel="stylesheet" href="css/modal.css" />
    <link rel="stylesheet" href="css/search1.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="background-image: url('img/bck.png'); background-size: cover;">

<div class="page-container">
  <!-- HEADER NAVIGATION -->
  <header class="main-header">
    <nav class="header-nav">
      <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
      <div class="logo-container"><img src="img/logo2.png" alt="Logo" /></div>
      <ul class="nav-menu-horizontal" id="navMenu">
        <li><a href="admin.php" class="dropbtn"><i class="fa-solid fa-home"></i> Home</a></li>
        <li><a href="aboutAdmin.php" class="dropbtn">About</a></li>
        <?php foreach($typeMap as $typeID => $typeName): ?>
          <li><a href="<?= strtolower($typeName) ?>.php" class="dropbtn"><?= htmlspecialchars($typeName) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <button id="searchIconBtn" class="search-icon-btn" type="button" title="Search Projects"><i class="fa-solid fa-magnifying-glass"></i></button>
      <div class="admin-actions">
        <button id="editProjectBtn" type="button" title="Edit Project"><i class="fa-solid fa-pen-to-square" style="font-size: medium;"></i></button>
        <button id="addProjectBtn" type="button" title="Add New Project"><i class="fa-solid fa-plus" style="font-size: medium;"></i></button>
        <button id="toggleDeleteBtn" type="button" title="Delete Project"><i class="fa-solid fa-trash-can" style="font-size: medium;"></i></button>
        <button id="logoutBtn" type="button" title="Logout"><i class="fa-solid fa-right-from-bracket" style="font-size: medium;"></i></button>
      </div>
    </nav>
  </header>

  <!-- SEARCH OVERLAY -->
  <div id="searchOverlay" class="search-overlay">
    <div class="search-overlay-content">
      <div class="search-container">
        <input type="text" id="projectSearch" placeholder="Search system..." autocomplete="off">
        <button class="search-close-btn" id="searchCloseBtn"><i class="fa-solid fa-xmark"></i></button>
        <div id="searchResults" class="search-results"></div>
      </div>
    </div>
  </div>

  <?php if (isset($_GET['updated'])): ?>
    <div class="success-message">
      <i class="fa-solid fa-check-circle"></i> Category updated successfully!
    </div>
  <?php endif; ?>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <div class="welcome-section">
      <h1><?= htmlspecialchars($SQLtitle) ?></h1>
      <p>Admin page to add/update/delete project</p>
    </div>

    <!-- CATEGORY CARDS -->
    <div class="category-cards">
      <?php foreach ($typeMap as $typeID => $typeName): 
        $custom = $categoryCustom[$typeID] ?? null;
        $icon = $custom['icon'] ?? 'fa-diagram-project';
        $description = $custom['description'] ?? $typeName . '-related systems.';
        $bgImage = $custom['bg_image'] ?? '';
        $bgStyle = $bgImage ? "background-image: url('img/{$bgImage}');" : '';
      ?>
        <div class="category-card <?= strtolower($typeName) ?>" style="<?= $bgStyle ?>">
          <button class="edit-category-btn" onclick="openEditCategory(<?= $typeID ?>, '<?= htmlspecialchars($typeName) ?>', '<?= htmlspecialchars($icon) ?>', '<?= htmlspecialchars($description) ?>', '<?= htmlspecialchars($bgImage) ?>')">
            <i class="fa-solid fa-edit"></i>
          </button>
          <div class="category-icon"><i class="fa-solid <?= htmlspecialchars($icon) ?>"></i></div>
          <div class="category-count" data-target="<?= $projectCounts[$typeID] ?? 0 ?>">
            <div class="count-label">Projects</div>
            <div class="count">0</div>
          </div>
          <div class="category-title"><?= htmlspecialchars($typeName) ?> Projects</div>
          <div class="category-description"><?= htmlspecialchars($description) ?></div>
          <div><a href="<?= strtolower($typeName) ?>.php" class="category-link">View Projects <i class="fa-solid fa-arrow-right"></i></a></div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>

  <footer>
    <span><i class="fa-regular fa-copyright"></i> 2025 Copyright: <b>Alps Electric (Malaysia) Sdn Bhd</b></span>
  </footer>
</div>

<!-- EDIT CATEGORY MODAL -->
<div id="editCategoryModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close-modal" onclick="closeModal()">&times;</span>
      <h2>Edit Category Card</h2>
    </div>
    <div class="modal-body">
      <form method="POST" action="">
        <input type="hidden" name="update_category" value="1">
        <input type="hidden" name="type_id" id="edit_type_id">
        
        <div class="form-group">
          <label>Category: <span id="category_name"></span></label>
        </div>

        <div class="form-group">
          <label for="edit_icon">
            <i class="fa-solid fa-icons"></i> Icon (FontAwesome class)
          </label>
          <input type="text" name="icon" id="edit_icon" placeholder="e.g., fa-industry, fa-building, fa-cogs" required>
          <div class="icon-preview" id="icon_preview">
            <i class="fa-solid fa-diagram-project"></i>
          </div>
          
          <div class="icon-suggestions">
            <?php 
            $suggestedIcons = [
              'fa-industry', 'fa-building', 'fa-cogs', 'fa-chart-line', 'fa-diagram-project',
              'fa-laptop-code', 'fa-network-wired', 'fa-server', 'fa-database', 'fa-microchip',
              'fa-robot', 'fa-sitemap', 'fa-project-diagram', 'fa-tools', 'fa-wrench',
              'fa-clipboard-check', 'fa-tasks', 'fa-file-alt', 'fa-folder-open', 'fa-briefcase'
            ];
            foreach ($suggestedIcons as $iconClass): ?>
              <div class="icon-suggestion" onclick="selectIcon('<?= $iconClass ?>')">
                <i class="fa-solid <?= $iconClass ?>"></i>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="form-group">
          <label for="edit_description">
            <i class="fa-solid fa-align-left"></i> Description
          </label>
          <textarea name="description" id="edit_description" required></textarea>
        </div>

        <div class="form-group">
          <label for="edit_bg_image">
            <i class="fa-solid fa-image"></i> Background Image (optional)
          </label>
          <select name="bg_image" id="edit_bg_image">
            <option value="">No background</option>
            <?php foreach ($bgImages as $img): ?>
              <option value="<?= htmlspecialchars($img) ?>"><?= htmlspecialchars($img) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="bg-image-preview" id="bg_preview"></div>
        </div>

        <button type="submit" class="submit-btn">
          <i class="fa-solid fa-save"></i> Save Changes
        </button>
      </form>
    </div>
  </div>
</div>

<!-- ADD / EDIT / DELETE MODALS -->
<?php include 'modal.php'; ?>

<script src="js/scriptModal.js"></script>
<script src="js/scriptCount.js"></script>
<script src="js/scriptFilter.js"></script>
<script>
  const allProjects = [
    <?php foreach ($allProjectsList as $index => $p): ?>
      <?= $index>0?',' : '' ?>{
        id: <?= $p['id'] ?>,
        name: "<?= addslashes($p['name']) ?>",
        ip: "<?= $p['ip'] ?>",
        status: <?= $p['status'] ?>,
        type: "<?= addslashes($p['typeName']) ?>",
        image: "<?= $p['image'] ?>"
      }
    <?php endforeach; ?>
  ];

  // ================== EDIT CATEGORY MODAL ==================
  function openEditCategory(typeId, typeName, icon, description, bgImage) {
    document.getElementById('edit_type_id').value = typeId;
    document.getElementById('category_name').textContent = typeName;
    document.getElementById('edit_icon').value = icon;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_bg_image').value = bgImage;
    
    updateIconPreview(icon);
    updateBgPreview(bgImage);
    
    document.getElementById('editCategoryModal').style.display = 'block';
  }

  function closeModal() {
    document.getElementById('editCategoryModal').style.display = 'none';
  }

  function selectIcon(iconClass) {
    document.getElementById('edit_icon').value = iconClass;
    updateIconPreview(iconClass);
  }

  function updateIconPreview(iconClass) {
    const preview = document.getElementById('icon_preview');
    preview.innerHTML = `<i class="fa-solid ${iconClass}"></i>`;
  }

  function updateBgPreview(imageName) {
    const preview = document.getElementById('bg_preview');
    if (imageName) {
      preview.style.backgroundImage = `url('img/${imageName}')`;
      preview.style.display = 'block';
    } else {
      preview.style.display = 'none';
    }
  }

  // Icon input change
  document.getElementById('edit_icon').addEventListener('input', function() {
    updateIconPreview(this.value);
  });

  // Background image select change
  document.getElementById('edit_bg_image').addEventListener('change', function() {
    updateBgPreview(this.value);
  });

  // Close modal when clicking outside
  window.onclick = function(event) {
    const modal = document.getElementById('editCategoryModal');
    if (event.target === modal) {
      closeModal();
    }
  }

  // Auto-hide success message
  setTimeout(() => {
    const msg = document.querySelector('.success-message');
    if (msg) {
      msg.style.opacity = '0';
      msg.style.transition = 'opacity 0.5s';
      setTimeout(() => msg.remove(), 500);
    }
  }, 3000);

  // ================== SEARCH FUNCTIONALITY ==================
  const searchInput = document.getElementById('projectSearch');
  const searchResults = document.getElementById('searchResults');
  searchInput.addEventListener('input', function() {
    const query = this.value.trim().toLowerCase();
    if (!query) { searchResults.classList.remove('active'); return; }
    const filtered = allProjects.filter(p =>
      p.name.toLowerCase().includes(query) || p.type.toLowerCase().includes(query)
    );
    if (filtered.length) {
      searchResults.innerHTML = filtered.map(p => `
        <div class="search-result-item" onclick="window.open('http://${p.ip}', '_blank')">
          <img src="data:image/jpeg;base64,${p.image}" alt="${p.name}" class="search-result-image">
          <div class="search-result-info">
            <div class="search-result-name">${p.name}</div>
            <div class="search-result-type">${p.type}</div>
          </div>
          <div class="search-result-status ${p.status==1?'active':'inactive'}"></div>
        </div>
      `).join('');
      searchResults.classList.add('active');
    } else {
      searchResults.innerHTML = '<div class="no-results">No projects found</div>';
      searchResults.classList.add('active');
    }
  });

  // ================== HAMBURGER & NAVIGATION ==================
  const hamburger = document.getElementById('hamburger');
  const navMenu = document.getElementById('navMenu');
  hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
  });

  document.querySelectorAll(".dropbtn").forEach(btn =>
    btn.addEventListener("click", () => {
      const parent = btn.parentElement;
      document.querySelectorAll(".dropdown").forEach(d => { if(d!==parent) d.classList.remove("open"); });
      parent.classList.toggle("open");
    })
  );
</script>
</body>
</html>
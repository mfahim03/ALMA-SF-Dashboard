<?php
include 'db.php';

// -------------------- FETCH TYPES --------------------
$typeMap = [];
$sqlTypes = "SELECT TypeID, Type FROM Type";
$stmtTypes = sqlsrv_query($conn, $sqlTypes);
if ($stmtTypes) {
    while ($row = sqlsrv_fetch_array($stmtTypes, SQLSRV_FETCH_ASSOC)) {
        $typeMap[$row['TypeID']] = $row['Type'];
    }
}

// -------------------- FETCH PROJECTS --------------------
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

// Default counts (0 if type not exists)
$countGeneral = $projectCounts[1] ?? 0;
$countDirect = $projectCounts[2] ?? 0;
$countIndirect = $projectCounts[3] ?? 0;
$countAudit = $projectCounts[4] ?? 0;

$SQLtitle = "ALMA SF Admin";

sqlsrv_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
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
        <li><a href="about.php" class="dropbtn">About</a></li>
        <?php foreach($typeMap as $typeID => $typeName): ?>
          <li><a href="<?= strtolower($typeName) ?>.php" class="dropbtn <?= strtolower($typeName)=='direct'?'active':'' ?>"><?= htmlspecialchars($typeName) ?></a></li>
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

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <div class="welcome-section">
      <h1><?= htmlspecialchars($SQLtitle) ?></h1>
      <p>Select a category to view projects</p>
    </div>

    <!-- CATEGORY CARDS -->
    <div class="category-cards">
      <?php foreach ($typeMap as $typeID => $typeName): ?>
        <div class="category-card <?= strtolower($typeName) ?>">
          <div class="category-icon"><i class="fa-solid fa-diagram-project"></i></div>
          <div class="category-count" data-target="<?= $projectCounts[$typeID] ?? 0 ?>">
            <div class="count-label">Projects</div>
            <div class="count">0</div>
          </div>
          <div class="category-title"><?= htmlspecialchars($typeName) ?> Projects</div>
          <div class="category-description"><?= htmlspecialchars($typeName) ?>-related systems.</div>
          <div><a href="<?= strtolower($typeName) ?>.php" class="category-link">View Projects <i class="fa-solid fa-arrow-right"></i></a></div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>

  <footer>
    <span><i class="fa-regular fa-copyright"></i> 2025 Copyright: <b>Alps Electric (Malaysia) Sdn Bhd</b></span>
  </footer>
</div>

<!-- ADD / EDIT / DELETE MODALS -->
<!-- Add Project -->
<div id="addProjectModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2>Add New Project</h2>
    <form id="addProjectForm" action="add_project.php" method="post" enctype="multipart/form-data">
      <label for="name">Project Name:</label>
      <input type="text" id="name" name="name" required>
      <label for="ip"><i class='fas fa-laptop-house'></i> IP Address:</label>
      <input type="text" id="ip" name="ip" required>
      <label for="status">Status:</label>
      <select id="status" name="status">
        <option value="1">Active</option>
        <option value="2">Inactive</option>
      </select>
      <label for="type">Type:</label>
      <select id="type" name="type">
        <?php foreach($typeMap as $typeID => $typeName): ?>
          <option value="<?= $typeID ?>"><?= htmlspecialchars($typeName) ?></option>
        <?php endforeach; ?>
      </select>
      <label for="image"><i class='fas fa-camera'></i> Project Image: <span style="color:#888;">(Optional)</span></label>
      <input type="file" id="image" name="image" accept="image/*">
      <button type="submit" name="submit">Add Project</button>
    </form>
  </div>
</div>

<!-- Edit Project -->
<div id="editProjectModal" class="modal">
  <div class="modal-content">
    <span class="close-edit">&times;</span>
    <h2>Edit Project</h2>
    <form id="editProjectForm" action="edit_project.php" method="post" enctype="multipart/form-data">
      <label>Select Project:</label>
      <select id="project_id" name="id" required>
        <?php foreach($allProjectsList as $p): ?>
          <option value="<?= $p['id'] ?>"
                  data-name="<?= htmlspecialchars($p['name']) ?>"
                  data-ip="<?= htmlspecialchars($p['ip']) ?>"
                  data-status="<?= $p['status'] ?>"
                  data-type="<?= $p['TypeID'] ?>"
                  data-image="<?= htmlspecialchars($p['image']) ?>">
            <?= htmlspecialchars($p['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <label>Project Name:</label>
      <input type="text" id="edit_name" name="name" required>
      <label><i class='fas fa-laptop-house'></i> IP Address:</label>
      <input type="text" id="edit_ip" name="ip" required>
      <label>Status:</label>
      <select id="edit_status" name="status">
        <option value="1">Active</option>
        <option value="2">Inactive</option>
      </select>
      <label>Type:</label>
      <select id="edit_type" name="type">
        <?php foreach($typeMap as $typeID => $typeName): ?>
          <option value="<?= $typeID ?>"><?= htmlspecialchars($typeName) ?></option>
        <?php endforeach; ?>
      </select>
      <div id="current_image_container" style="margin:15px 0;">
        <label>Current Image:</label>
        <div style="border:1px solid #ddd; padding:10px; border-radius:4px; text-align:center; background:#f9f9f9;">
          <img id="current_image_preview" src="" alt="Current project image" style="max-width:200px; max-height:200px; display:block; margin:0 auto;">
        </div>
      </div>
      <label for="edit_image">Change Image: <span style="color:#888;">(Optional)</span></label>
      <input type="file" id="edit_image" name="image" accept="image/*">
      <div id="new_image_preview_container" style="margin:10px 0; display:none;">
        <label>New Image Preview:</label>
        <div style="border:1px solid #ddd; padding:10px; border-radius:4px; text-align:center; background:#f0f8ff;">
          <img id="new_image_preview" src="" alt="New image preview" style="max-width:200px; max-height:200px; display:block; margin:0 auto;">
        </div>
      </div>
      <button type="submit">Update Project</button>
    </form>
  </div>
</div>

<!-- Delete Project -->
<div id="deleteProjectModal" class="modal">
  <div class="modal-content">
    <span class="close-delete">&times;</span>
    <h2>Delete Project</h2>
    <form id="deleteProjectForm" action="delete_project.php" method="post">
      <label>Select Project to Delete:</label>
      <select id="delete_project_id" name="id" required>
        <?php foreach($allProjectsList as $p): ?>
          <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" style="background:#c0392b;">Delete Project</button>
    </form>
  </div>
</div>

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

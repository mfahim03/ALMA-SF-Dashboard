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
function fetchProjectsByType($conn, $typeID, $typeMap) {
    $sql = "SELECT id, name, image, status, type AS TypeID, ip FROM Projects WHERE type = ?";
    $params = [$typeID];
    $stmt = sqlsrv_query($conn, $sql, $params);
    $projects = [];
    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $row['typeName'] = $typeMap[$row['TypeID']] ?? 'Unknown';
            $projects[] = $row;
        }
    }
    return $projects;
}

// -------------------- PAGE VARIABLES --------------------
$typeID = 3; 
$projects = fetchProjectsByType($conn, $typeID, $typeMap);

// Fetch all projects for search & modals
$allProjectsList = [];
foreach ($typeMap as $id => $name) {
    $allProjectsList = array_merge($allProjectsList, fetchProjectsByType($conn, $id, $typeMap));
}

$SQLtitle = "ALMA SF Dashboard";

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
        <li><a href="aboutAdmin.php" class="dropbtn">About</a></li>
        <?php foreach($typeMap as $id => $name): ?>
          <li><a href="<?= strtolower($name) ?>.php" class="dropbtn <?= $id==$typeID?'active':'' ?>"><?= htmlspecialchars($name) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <button id="searchIconBtn" class="search-icon-btn" type="button" title="Search Projects"><i class="fa-solid fa-magnifying-glass"></i></button>
      <div class="admin-actions">
        <button id="editProjectBtn" type="button" title="Edit Project"><i class="fa-solid fa-pen-to-square"></i></button>
        <button id="addProjectBtn" type="button" title="Add New Project"><i class="fa-solid fa-plus"></i></button>
        <button id="toggleDeleteBtn" type="button" title="Delete Project"><i class="fa-solid fa-trash-can"></i></button>
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
      <h1><i class="fa-solid fa-diagram-project"></i> <?= htmlspecialchars($SQLtitle) ?></h1>
      <p>For ALMA use</p>
    </div>

    <div class="projects">
      <div class="top-sections">
        <div class="section-box" style="flex:1 1 100%;">
          <h3><?= htmlspecialchars($SQLtitle) ?> (<?= count($projects) ?>)</h3>
          <div class="project-group">
            <?php if (!empty($projects)): ?>
              <?php foreach ($projects as $project):
                $statusColor = $project['status']==1?'background-color:green;':'background-color:orange;';
                $img = 'data:image/jpeg;base64,' . $project['image'];
              ?>
                <a class="project-card" href="http://<?= htmlspecialchars($project['ip']) ?>" target="_blank" data-project-id="<?= $project['id'] ?>">
                  <div class="project-edit-icon" onclick="openEditForProject(event, <?= $project['id'] ?>)">
                    <i class="fa-solid fa-pen-clip"></i>
                  </div>
                  <img src="<?= $img ?>" alt="<?= htmlspecialchars($project['name']) ?>">
                  <div class="project-name" style="<?= $statusColor ?>"><?= htmlspecialchars($project['name']) ?></div>
                </a>
              <?php endforeach; ?>
            <?php else: ?>
              <p>No projects found.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer>
        <span><i class="fa-regular fa-copyright"></i> 2025 Copyright: <b>Alps Electric (Malaysia) Sdn Bhd</b></span>
  </footer>
</div>

<!-- MODALS (Add/Edit/Delete) -->
<?php include 'modal.php'; ?> <!-- Keep your modal code in a separate file -->

<script src="js/scriptModal.js"></script>
<script src="js/scriptFilter.js"></script>
<script>
  // ================== ALL PROJECTS FOR SEARCH ==================
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

  // ================== GLOBAL FUNCTION: OPEN EDIT MODAL ==================
  function openEditForProject(event, projectId) {
    event.preventDefault();
    event.stopPropagation();
    
    const modal = document.getElementById('editProjectModal');
    const select = document.getElementById('project_id');
    
    if (!select) {
      console.error('Edit modal select element not found');
      return;
    }
    
    // Set the project ID in the select dropdown
    select.value = projectId;
    
    // Trigger change event to populate other fields
    const changeEvent = new Event('change', { bubbles: true });
    select.dispatchEvent(changeEvent);
    
    // Show the modal
    if (modal) {
      modal.style.display = 'block';
    } else {
      console.error('Edit modal not found');
    }
  }

  // ================== SEARCH FUNCTIONALITY ==================
  const searchInput = document.getElementById('projectSearch');
  const searchResults = document.getElementById('searchResults');

  searchInput.addEventListener('input', function() {
    const query = this.value.trim().toLowerCase();
    if (!query) { 
      searchResults.classList.remove('active'); 
      return; 
    }

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

  // ================== HAMBURGER MENU ==================
  const hamburger = document.getElementById('hamburger');
  const navMenu = document.getElementById('navMenu');

  hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
  });

  // ================== SEARCH OVERLAY CONTROLS ==================
  const searchIconBtn = document.getElementById('searchIconBtn');
  const searchOverlay = document.getElementById('searchOverlay');
  const searchCloseBtn = document.getElementById('searchCloseBtn');

  searchIconBtn.addEventListener('click', () => {
    searchOverlay.classList.add('active');
    searchInput.focus();
  });

  searchCloseBtn.addEventListener('click', () => {
    searchOverlay.classList.remove('active');
    searchInput.value = '';
    searchResults.classList.remove('active');
  });

  // Close on escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
      searchOverlay.classList.remove('active');
      searchInput.value = '';
      searchResults.classList.remove('active');
    }
  });

  // Close when clicking outside
  searchOverlay.addEventListener('click', (e) => {
    if (e.target === searchOverlay) {
      searchOverlay.classList.remove('active');
      searchInput.value = '';
      searchResults.classList.remove('active');
    }
  });

  // ================== LOGOUT ==================
  document.getElementById('logoutBtn').addEventListener('click', () => {
    if (confirm("Are you sure you want to log out?")) {
      window.location.href = "index.php";
    }
  });
</script>
</body>
</html>

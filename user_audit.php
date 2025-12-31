<?php
include 'db.php';
include 'login.php';

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

// -------------------- FETCH ALL PROJECTS --------------------
function fetchAllProjects($conn, $typeMap) {
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

// -------------------- FETCH PROJECTS BY TYPE --------------------
$projectsByType = [];
foreach ($typeMap as $typeID => $typeName) {
    $projectsByType[$typeID] = fetchProjectsByType($conn, $typeID, $typeMap);
}

$allProjectsList = fetchAllProjects($conn, $typeMap);

// -------------------- PAGE VARIABLES --------------------
$typeID = 4; 
$projects = fetchProjectsByType($conn, $typeID, $typeMap);

// Fetch all projects for search & modals
$allProjectsList = [];
foreach ($typeMap as $id => $name) {
    $allProjectsList = array_merge($allProjectsList, fetchProjectsByType($conn, $id, $typeMap));
}

$SQLtitle = "ALMA SF Dashboard";
$loginError = isset($_GET['login_error']) && isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
unset($_SESSION['login_error']);

sqlsrv_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
  <title><?= htmlspecialchars($SQLtitle) ?></title>
  <link rel="icon" type="image/png" href="img/alps.png">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="css/index1.css" />
  <link rel="stylesheet" href="css/chatbox.css" />
  <link rel="stylesheet" href="css/search1.css" />
  <link rel="stylesheet" href="css/modal.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="background-image: url('img/bck.png'); background-size: cover;">

<div class="page-container">
  <header class="main-header">
    <nav class="header-nav">
      <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
      <div class="logo-container"><img src="img/logo2.png" alt="Logo" /></div>

      <ul class="nav-menu-horizontal" id="navMenu">
                <li><a href="index.php" class="dropbtn"><i class="fa-solid fa-home"></i> Home</a></li>
                <li><a href="about.php" class="dropbtn">About</a></li>
                <?php foreach ($typeMap as $typeID => $typeName): ?>
                    <li class="dropdown">
                        <button type="button" class="dropbtn">
                            <?= htmlspecialchars($typeName) ?> 
                            <i class="fa-solid fa-angle-down caret"></i>
                        </button>
                        <div class="dropdown-content">
                            <?php if (!empty($projectsByType[$typeID])): ?>
                                <?php foreach ($projectsByType[$typeID] as $project): ?>
                                    <a href="http://<?= htmlspecialchars($project['ip']) ?>" target="_blank">
                                        <?= htmlspecialchars($project['name']) ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="padding:10px; color:#999;">No projects available</div>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

      <div class="header-actions">
        <button id="searchIconBtn" class="search-icon-btn" type="button" title="Search Projects">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
        <button class="admin-login-btn" id="adminLoginBtn">
          <i class="fa-regular fa-user"></i> Login as Admin
        </button>
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
      <h1><i class="fa-solid fa-clipboard-check"></i> Audit Projects </h1>
      <p>For Audit-related systems</p>
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

<!-- Floating AEN gif -->
<div id="aen-bubble" class="gif-bubble"><img src="img/adEN.gif" alt="AEN Assistant"></div>

<!-- Chat Container -->
<div id="chatContainer" class="chat-container">
  <div class="chat-box" id="chatBox">
    <div class="message bot">👋 Hi! I'm A-den, your virtual assistant.</div>
    <div class="message bot">How can I help you today?</div>
  </div>
  <div style="display:flex;">
    <input type="text" id="userInput" placeholder="Type your message here..." />
    <button onclick="sendMessage()">Send</button>
  </div>
</div>

<div id="loginModal" class="modal">
  <div class="login-modal-content">
    <div class="login-modal-header">
      <span class="close-login">&times;</span>
      <h2>Login as Admin</h2>
    </div>
    <div class="login-modal-body">
      <?php if (!empty($loginError)): ?>
        <div class="login-error"><?= htmlspecialchars($loginError) ?></div>
      <?php endif; ?>
      <form method="POST" action="login.php">
        <div class="login-form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required autocomplete="username">
        </div>
        <div class="login-form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <button type="submit" name="login" class="login-submit-btn">Login</button>
      </form>
    </div>
  </div>
</div>


<script src="js/scriptChatBox.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const searchIconBtn = document.getElementById('searchIconBtn');
  const searchOverlay = document.getElementById('searchOverlay');
  const searchCloseBtn = document.getElementById('searchCloseBtn');
  const searchInput = document.getElementById('projectSearch');
  const searchResults = document.getElementById('searchResults');

  if (!searchIconBtn || !searchOverlay || !searchInput || !searchResults) return;

  // Open overlay
  searchIconBtn.addEventListener('click', () => {
    searchOverlay.classList.add('active');
    searchInput.focus();
  });

  // Close overlay by X button
  searchCloseBtn.addEventListener('click', () => {
    searchOverlay.classList.remove('active');
    searchInput.value = '';
    searchResults.classList.remove('active');
  });

  // Close overlay by Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
      searchOverlay.classList.remove('active');
      searchInput.value = '';
      searchResults.classList.remove('active');
    }
  });

  // Close overlay by clicking outside the search box
  searchOverlay.addEventListener('click', (e) => {
    if (e.target === searchOverlay) {
      searchOverlay.classList.remove('active');
      searchInput.value = '';
      searchResults.classList.remove('active');
    }
  });

  // ================== ALL PROJECTS FOR SEARCH ==================
  const allProjects = [
    <?php foreach ($allProjectsList as $index => $p): ?>
      <?= $index>0 ? ',' : '' ?>{
        id: <?= $p['id'] ?>,
        name: "<?= addslashes($p['name']) ?>",
        ip: "<?= $p['ip'] ?>",
        status: <?= $p['status'] ?>,
        type: "<?= addslashes($p['typeName']) ?>",
        image: "<?= $p['image'] ?>"
      }
    <?php endforeach; ?>
  ];

  // ================== LIVE SEARCH ==================
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
        <div class="search-result-item" onclick="window.open('http://${p.ip}','_blank')">
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
});

const loginModal = document.getElementById("loginModal");
const adminLoginBtn = document.getElementById("adminLoginBtn");
const closeLogin = document.querySelector(".close-login");

adminLoginBtn.onclick = () => loginModal.style.display = "block";
closeLogin.onclick = () => loginModal.style.display = "none";
window.onclick = e => { if (e.target === loginModal) loginModal.style.display = "none"; };

// Show modal automatically if there was a login error
<?php if (!empty($loginError)): ?>loginModal.style.display = "block";<?php endif; ?>

</script>

</body>
</html>
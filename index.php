<?php
session_start();
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

// -------------------- FETCH PROJECTS FUNCTION --------------------
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

// -------------------- FETCH CATEGORY CUSTOMIZATIONS --------------------
$categoryCustom = [];
$sqlCustom = "SELECT type_id, icon, description, bg_image FROM CategoryCustomization";
$stmtCustom = sqlsrv_query($conn, $sqlCustom);
if ($stmtCustom) {
    while ($row = sqlsrv_fetch_array($stmtCustom, SQLSRV_FETCH_ASSOC)) {
        $categoryCustom[$row['type_id']] = $row;
    }
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

// -------------------- COUNT PROJECTS BY TYPE --------------------
$projectCounts = [];
foreach ($typeMap as $typeID => $typeName) {
    $projectCounts[$typeID] = count($projectsByType[$typeID]);
}

$SQLtitle = "ALMA SF Dashboard";
$SQLDesc = "";

$loginError = isset($_GET['login_error']) && isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
unset($_SESSION['login_error']);

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
    <link rel="stylesheet" href="css/index1.css" />
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
            <div class="hamburger" id="hamburger">
                <span></span><span></span><span></span>
            </div>
            <div class="logo-container">
                <img src="img/logo2.png" alt="Logo" />
            </div>

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
                <button class="search-close-btn" id="searchCloseBtn">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <div id="searchResults" class="search-results"></div>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="welcome-section">
            <h1>ALMA Smart Factory Dashboard</h1>
            <p><?= htmlspecialchars($SQLDesc) ?></p>
        </div>

           <div class="category-cards">
  <?php foreach ($typeMap as $typeID => $typeName): 
    $custom = $categoryCustom[$typeID] ?? null;
    $icon = $custom['icon'] ?? 'fa-diagram-project';
    $description = $custom['description'] ?? $typeName . ' systems.';
    $bgImage = $custom['bg_image'] ?? '';
    $bgStyle = $bgImage ? "background-image: url('img/{$bgImage}');" : '';
  ?>
    <div class="category-card <?= strtolower($typeName) ?>" style="<?= $bgStyle ?>">
      <div class="category-icon"><i class="fa-solid <?= htmlspecialchars($icon) ?>"></i></div>
      <div class="category-count" data-target="<?= $projectCounts[$typeID] ?? 0 ?>">
        <div class="count-label">Projects</div>
        <div class="count">0</div>
      </div>
      <div class="category-title"><?= htmlspecialchars($typeName) ?> Projects</div>
      <div class="category-description"><?= htmlspecialchars($description) ?></div>

      <!-- 🔗 link to user page -->
      <div>
        <a href="user_<?= strtolower($typeName) ?>.php" class="category-link">
          View Projects <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
    </div>
  <?php endforeach; ?>
</div>

    </main>

    <footer>
        <span><i class="fa-regular fa-copyright"></i> 2025 Copyright: <b>Alps Electric (Malaysia) Sdn Bhd</b></span>
    </footer>
</div>

<!-- Floating AEN gif -->
<div id="aen-bubble" class="gif-bubble">
    <img src="img/adEN.gif" alt="AEN Assistant">
</div>

<!-- Chat Container -->
<div id="chatContainer" class="chat-container">
    <div class="chat-box" id="chatBox">
        <div class="message bot">👋 Hi! I'm A-den, your virtual assistant.</div>
        <div class="message bot">How can I help you today?</div>
    </div>
    <div style="display: flex; padding: 15px;">
        <input type="text" id="userInput" placeholder="Type your message here..." style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px 0 0 5px;">
        <button onclick="sendMessage()" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 0 5px 5px 0; cursor: pointer;">Send</button>
    </div>
</div>

<!-- LOGIN MODAL -->
<div id="loginModal" class="modal">
    <div class="login-modal-content">
        <div class="login-modal-header">
            <span class="close-login">&times;</span>
            <h2>Login as Admin</h2>
        </div>
        <div class="login-modal-body">
            <?php if (!empty($loginError)): ?>
                <div class="login-error">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($loginError) ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="login-form-group">
                    <label for="username"><i class="fa-solid fa-user"></i> Username</label>
                    <input type="text" id="username" name="username" required autocomplete="username">
                </div>
                <div class="login-form-group">
                    <label for="password"><i class="fa-solid fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" name="login" class="login-submit-btn">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </button>
            </form>
        </div>
    </div>
</div>

<script src="js/scriptChatBox.js"></script>
<script src="js/scriptCount.js"></script>
<script>
    // ================== ALL PROJECTS DATA ==================
    const allProjects = [
        <?php foreach ($allProjectsList as $index => $p): ?>
            <?= $index > 0 ? ',' : '' ?>{
                id: <?= $p['id'] ?>,
                name: "<?= addslashes($p['name']) ?>",
                ip: "<?= addslashes($p['ip']) ?>",
                status: <?= $p['status'] ?>,
                type: "<?= addslashes($p['typeName']) ?>",
                image: "<?= $p['image'] ?>"
            }
        <?php endforeach; ?>
    ];

    // ================== HAMBURGER MENU ==================
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');

    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
    });

    // ================== DROPDOWN TOGGLE ==================
    document.querySelectorAll(".dropbtn").forEach(btn => {
        btn.addEventListener("click", (e) => {
            const parent = btn.parentElement;
            document.querySelectorAll(".dropdown").forEach(d => {
                if (d !== parent) d.classList.remove("open");
            });
            parent.classList.toggle("open");
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('open'));
        }
    });

    // ================== SEARCH OVERLAY ==================
    const searchIconBtn = document.getElementById('searchIconBtn');
    const searchOverlay = document.getElementById('searchOverlay');
    const searchCloseBtn = document.getElementById('searchCloseBtn');
    const searchInput = document.getElementById('projectSearch');
    const searchResults = document.getElementById('searchResults');

    searchIconBtn.addEventListener('click', () => {
        searchOverlay.classList.add('active');
        searchInput.focus();
    });

    searchCloseBtn.addEventListener('click', () => {
        searchOverlay.classList.remove('active');
        searchInput.value = '';
        searchResults.classList.remove('active');
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
            searchOverlay.classList.remove('active');
            searchInput.value = '';
            searchResults.classList.remove('active');
        }
    });

    searchOverlay.addEventListener('click', (e) => {
        if (e.target === searchOverlay) {
            searchOverlay.classList.remove('active');
            searchInput.value = '';
            searchResults.classList.remove('active');
        }
    });

    // ================== SEARCH FUNCTIONALITY ==================
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
                    <div class="search-result-status ${p.status == 1 ? 'active' : 'inactive'}"></div>
                </div>
            `).join('');
            searchResults.classList.add('active');
        } else {
            searchResults.innerHTML = '<div class="no-results">No projects found</div>';
            searchResults.classList.add('active');
        }
    });

    // ================== LOGIN MODAL ==================
    const loginModal = document.getElementById("loginModal");
    const adminLoginBtn = document.getElementById("adminLoginBtn");
    const closeLogin = document.querySelector(".close-login");

    adminLoginBtn.onclick = () => loginModal.style.display = "block";
    closeLogin.onclick = () => loginModal.style.display = "none";
    
    window.onclick = (e) => {
        if (e.target === loginModal) {
            loginModal.style.display = "none";
        }
    };

    // Show modal if there's a login error
    <?php if (!empty($loginError)): ?>
        loginModal.style.display = "block";
    <?php endif; ?>
</script>
</body>
</html>
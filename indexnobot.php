<?php
session_start();
include 'db.php';
include 'login.php';

$projects = fetchProjects($conn, 1);
$projectsIndirect = fetchProjects($conn, 2);
$projectsWIP = fetchProjects($conn, 3);
$projectsAudit = fetchProjects($conn, 4);

// ================== FETCH TITLE ==================
$SQLtitle = "AEN Dashboard Alt";
$SQLDesc = "Welcome to Advanced Engineering (AEN)";

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
  <link rel="stylesheet" href="css/modal.css" />
  <link rel="stylesheet" href="css/search.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="background-image: url('img/bck.png'); background-size: cover;">

<div class="page-container">
  <!-- HEADER NAVIGATION -->
  <header class="main-header">
    <nav class="header-nav">
      <div class="hamburger" id="hamburger">
        <span></span>
        <span></span>
        <span></span>
      </div>

      <div class="logo-container">
        <img src="img/logo2.png" alt="Logo" />
      </div>

      <ul class="nav-menu-horizontal" id="navMenu">
        <?php
        $sections = [
          "Manufacturing" => $projects,
          "Administration" => $projectsIndirect,
          "Audit" => $projectsAudit,
          "More" => $projectsWIP
        ];

        foreach ($sections as $title => $list): ?>
          <li class="dropdown">
            <button class="dropbtn"><?= htmlspecialchars($title) ?>
            <i class="fa-solid fa-angle-down caret"></i>
            </button>
            <div class="dropdown-content">
              <?php if (!empty($list)): ?>
                <?php foreach ($list as $project): ?>
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

      <button class="admin-login-btn" id="adminLoginBtn">
        <i class="fa-regular fa-user"></i> Login as Admin
      </button>
    </nav>
  </header>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <!-- WELCOME SECTION -->
    <div class="welcome-section">
      <h1><?= htmlspecialchars($SQLtitle) ?></h1>
      <p><?= htmlspecialchars($SQLDesc) ?></p>
      <p>We specialize in designing and developing innovative websites tailored to support both production and indirect operations. Our team is dedicated to crafting cutting-edge solutions that enhance efficiency and drive success in every project. Explore our services and discover how we can support your needs through technology and engineering excellence.</p>
    </div>

    <!-- SEARCH -->
    <div class="search-container">
      <input type="text" id="projectSearch" placeholder="Search system" autocomplete="off">
      <div id="searchResults" class="search-results"></div>
    </div>

    <!-- FILTER BUTTONS -->
    <div class="filter-buttons">
      <button class="filter-btn active" data-filter="all">All</button>
      <button class="filter-btn" data-filter="manufacturing">Manufacturing</button>
      <button class="filter-btn" data-filter="administration">Admin</button>
      <button class="filter-btn" data-filter="audits">Audits</button>
      <button class="filter-btn" data-filter="work in progress">Work in Progress</button>
    </div>

    <!-- PROJECT SECTIONS -->
    <div class="projects">
      <div class="top-sections">
      <?php
      $sections = [
        "Manufacturing" => $projects,
        "Administration" => $projectsIndirect,
        "Audits" => $projectsAudit,
        "Work In Progress" => $projectsWIP
      ];

      foreach ($sections as $title => $list): ?>
        <div class="section-box">
          <h3><?= htmlspecialchars($title) ?></h3>
          <div class="project-group">
            <?php if (!empty($list)): ?>
              <?php foreach ($list as $project): 
                $statusColor = $project['status'] == 1 ? 'background-color: green;' : 'background-color: orange;';
                $img = 'data:image/jpeg;base64,' . $project['image']; ?>
                <a class="project-card" href="http://<?= htmlspecialchars($project['ip']) ?>" target="_blank">
                  <img src="<?= $img ?>" alt="<?= htmlspecialchars($project['name']) ?>">
                  <div class="project-name" style="<?= $statusColor ?>">
                    <?= htmlspecialchars($project['name']) ?>
                  </div>
                </a>
              <?php endforeach; ?>
            <?php else: ?>
              <p>No <?= strtolower($title) ?> projects found.</p>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      </div>
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
  <div style="display: flex;">
    <input type="text" id="userInput" placeholder="Type your message here..." />
    <button onclick="sendMessage()">Send</button>
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
        <div class="login-error"><?= htmlspecialchars($loginError) ?></div>
      <?php endif; ?>
      <form method="POST" action="">
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

<script src="js/scriptFilter.js"></script>
<script src="js/scriptChatBox.js"></script>
<script>
  // ================== HAMBURGER MENU ==================
  const hamburger = document.getElementById('hamburger');
  const navMenu = document.getElementById('navMenu');

  hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
  });

  // ================== SEARCH ==================
  const allProjects = [
    <?php 
      $allProjectsList = array_merge($projects, $projectsIndirect, $projectsWIP, $projectsAudit);
      foreach($allProjectsList as $i => $p):
        $typeNames = [1=>'Manufacturing', 2=>'Administration', 3=>'Work In Progress', 4=>'Audits'];
        $type = $typeNames[$p['type']] ?? 'Unknown';
        echo ($i > 0 ? ',' : '') . json_encode([
          'id' => $p['id'],
          'name' => $p['name'],
          'ip' => $p['ip'],
          'status' => $p['status'],
          'type' => $type,
          'image' => $p['image']
        ]);
      endforeach;
    ?>
  ];

  const searchInput = document.getElementById('projectSearch');
  const searchResults = document.getElementById('searchResults');

  searchInput.addEventListener('input', () => {
    const query = searchInput.value.toLowerCase();
    if (!query) return searchResults.classList.remove('active');

    const filtered = allProjects.filter(p =>
      p.name.toLowerCase().includes(query) || p.type.toLowerCase().includes(query)
    );

    searchResults.innerHTML = filtered.length
      ? filtered.map(p => `
        <div class="search-result-item" onclick="window.open('http://${p.ip}', '_blank')">
          <img src="data:image/jpeg;base64,${p.image}" class="search-result-image">
          <div class="search-result-info">
            <div class="search-result-name">${p.name}</div> 
            <div class="search-result-type">${p.type}</div>
          </div>
          <div class="search-result-status ${p.status == 1 ? 'active' : 'inactive'}"></div>
        </div>
      `).join('') 
      : '<div class="no-results">No projects found</div>';
    searchResults.classList.add('active');
  });

  document.addEventListener('click', e => {
    if (!e.target.closest('.search-container')) searchResults.classList.remove('active');
  });

  // ================== DROPDOWNS ==================
  document.querySelectorAll(".dropbtn").forEach(btn => {
    btn.addEventListener("click", () => {
      const parent = btn.parentElement;
      document.querySelectorAll(".dropdown").forEach(d => {
        if (d !== parent) d.classList.remove("open");
      });
      parent.classList.toggle("open");
    });
  });

  // ================== LOGIN MODAL ==================
  const loginModal = document.getElementById("loginModal");
  const adminLoginBtn = document.getElementById("adminLoginBtn");
  const closeLogin = document.querySelector(".close-login");

  adminLoginBtn.onclick = () => loginModal.style.display = "block";
  closeLogin.onclick = () => loginModal.style.display = "none";
  window.onclick = e => { if (e.target === loginModal) loginModal.style.display = "none"; };
  <?php if (!empty($loginError)): ?>loginModal.style.display = "block";<?php endif; ?>

  // === TO DETERMINE FULL WIDTH FOR CONTAINER (MANUFACTURING)
  document.querySelectorAll('.section-box h3').forEach(h3 => {
    if(h3.textContent.includes('Manufacturing')){
      const box = h3.closest('.section-box');
      box.style.flex = '1 1 100%';
      box.style.maxWidth = '100%';
    }
  });
</script>
</body>
</html>
<?php
include 'db.php';
include 'login.php';

// -------------------- HANDLE FORM SUBMISSIONS --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Update About Section (unchanged)
    if ($action === 'updateAbout') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        
        $sqlUpdate = "UPDATE AboutPage SET Title = ?, Description = ? WHERE id = 1";
        $params = array($title, $description);
        $stmtUpdate = sqlsrv_prepare($conn, $sqlUpdate, $params);
        if (sqlsrv_execute($stmtUpdate)) {
            $_SESSION['message'] = "About section updated successfully!";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating about section.";
            $_SESSION['msg_type'] = "error";
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Add Team Member
    if ($action === 'addTeamMember') {
        $name = $_POST['member_name'] ?? '';
        $position = $_POST['member_position'] ?? '';
        $description = $_POST['member_description'] ?? '';
        $photo = null;  // Use null instead of empty string for better DB handling

        // Handle photo upload (convert to base64)
        if (!empty($_FILES['member_photo']['tmp_name'])) {
            $imageData = file_get_contents($_FILES['member_photo']['tmp_name']);
            $base64 = base64_encode($imageData);
            $mimeType = $_FILES['member_photo']['type'];

            $photo = "data:" . $mimeType . ";base64," . $base64;
        }

        if (!empty($name) && !empty($position)) {
            $sqlInsert = "INSERT INTO TeamMembers (name, position, description, photo) VALUES (?, ?, ?, ?)";
            $params = array($name, $position, $description, $photo);
            $stmtInsert = sqlsrv_prepare($conn, $sqlInsert, $params);
            if ($stmtInsert && sqlsrv_execute($stmtInsert)) {
                $_SESSION['message'] = "Team member added successfully!";
                $_SESSION['msg_type'] = "success";
            } else {
                $errors = sqlsrv_errors();
                error_log("Add Team Member SQL error: " . print_r($errors, true));
                $_SESSION['message'] = "Error adding team member.";
                $_SESSION['msg_type'] = "error";
            }
        } else {
            $_SESSION['message'] = "Name and Position are required.";
            $_SESSION['msg_type'] = "error";
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Update Team Member
    if ($action === 'updateTeamMember') {
        $memberId = $_POST['member_id'] ?? '';
        $name = $_POST['member_name'] ?? '';
        $position = $_POST['member_position'] ?? '';
        $description = $_POST['member_description'] ?? '';
        $photo = null;

        // Optional new photo update
        if (!empty($_FILES['member_photo']['tmp_name'])) {
            $imageData = file_get_contents($_FILES['member_photo']['tmp_name']);
            $base64 = base64_encode($imageData);
            $mimeType = $_FILES['member_photo']['type'];

            $photo = "data:" . $mimeType . ";base64," . $base64;

            $sqlUpdate = "UPDATE TeamMembers SET name = ?, position = ?, description = ?, photo = ? WHERE id = ?";
            $params = array($name, $position, $description, $photo, $memberId);
        } else {
            $sqlUpdate = "UPDATE TeamMembers SET name = ?, position = ?, description = ? WHERE id = ?";
            $params = array($name, $position, $description, $memberId);
        }

        $stmtUpdate = sqlsrv_prepare($conn, $sqlUpdate, $params);
        if ($stmtUpdate && sqlsrv_execute($stmtUpdate)) {
            $_SESSION['message'] = "Team member updated successfully!";
            $_SESSION['msg_type'] = "success";
        } else {
            $errors = sqlsrv_errors();
            error_log("Update Team Member SQL error: " . print_r($errors, true));
            $_SESSION['message'] = "Error updating team member.";
            $_SESSION['msg_type'] = "error";
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Delete Team Member (unchanged)
    if ($action === 'deleteTeamMember') {
        $memberId = $_POST['member_id'] ?? '';
        $sqlDelete = "DELETE FROM TeamMembers WHERE id = ?";
        $params = array($memberId);
        $stmtDelete = sqlsrv_prepare($conn, $sqlDelete, $params);
        if ($stmtDelete && sqlsrv_execute($stmtDelete)) {
            $_SESSION['message'] = "Team member deleted successfully!";
            $_SESSION['msg_type'] = "success";
        } else {
            $errors = sqlsrv_errors();
            error_log("Delete Team Member SQL error: " . print_r($errors, true));
            $_SESSION['message'] = "Error deleting team member.";
            $_SESSION['msg_type'] = "error";
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
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

// -------------------- FETCH ABOUT PAGE INFO --------------------
$SQLtitle = "ALMA SF Dashboard";
$SQLDesc = "Welcome to the Alps Electric (Malaysia) Sdn Bhd About Page.";

$sqlAbout = "SELECT Title, Description FROM AboutPage WHERE id = 1";
$stmtAbout = sqlsrv_query($conn, $sqlAbout);
if ($stmtAbout) {
    $row = sqlsrv_fetch_array($stmtAbout, SQLSRV_FETCH_ASSOC);
    if ($row) {
        $SQLtitle = $row['Title'];
        $SQLDesc = $row['Description'];
    }
}

// -------------------- FETCH TEAM MEMBERS --------------------
$teamMembers = [];
$sqlTeam = "SELECT * FROM TeamMembers ORDER BY id ASC";
$stmtTeam = sqlsrv_query($conn, $sqlTeam);
if ($stmtTeam) {
    while ($row = sqlsrv_fetch_array($stmtTeam, SQLSRV_FETCH_ASSOC)) {
        $teamMembers[] = $row;
    }
}

$message = $_SESSION['message'] ?? '';
$msgType = $_SESSION['msg_type'] ?? '';
unset($_SESSION['message'], $_SESSION['msg_type']);

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

sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= htmlspecialchars($SQLtitle) ?></title>
    <link rel="icon" type="image/png" href="img/alps.png">
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="css/admin.css" />
    <link rel="stylesheet" href="css/modal.css" />
    <link rel="stylesheet" href="css/search1.css" />
    <link rel="stylesheet" href="css/about.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body style="background-image: url('img/bck.png'); background-size: cover; background-repeat: no-repeat;">

<div class="page-container">
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

    <main class="main-content">
        <?php if (!empty($message)): ?>
            <div class="message <?= htmlspecialchars($msgType) ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <section class="about-section">
            <div class="about-text-container">
                <h1><?= htmlspecialchars($SQLtitle) ?></h1>
                <p><?= htmlspecialchars($SQLDesc) ?></p>
            </div>
            <button class="btn-action btn-edit" onclick="openEditAboutModal()">
                <i class="fa-solid fa-pen-to-square"></i> Edit About Section
            </button>
        </section>

        <section class="team-container">
            <h2>Our Team</h2>
            <button class="btn-action btn-add" onclick="openAddTeamMemberModal()"><i class="fa-solid fa-plus"></i> Add Team Member</button>

            <div class="team-grid">
                <?php if (!empty($teamMembers)): ?>
                    <?php foreach ($teamMembers as $member): ?>
                        <?php
                            $photoData = $member['photo'] ?? '';
                            if (empty($photoData)) {
                                $photoData = 'img/default-user.png';
                            }
                        ?>
                        <div class="team-card">
                            <div class="team-card-actions">
                                <button class="btn-action btn-edit" onclick="openEditTeamMemberModal(<?= htmlspecialchars(json_encode($member)) ?>)">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn-action btn-delete" onclick="deleteTeamMember(<?= $member['id'] ?>)">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                            <div class="team-photo-container">
                                <img src="<?= htmlspecialchars($photoData) ?>" alt="<?= htmlspecialchars($member['name']) ?>" class="team-photo">
                            </div>
                            <div class="team-info">
                                <h3><?= htmlspecialchars($member['name']) ?></h3>
                                <p class="team-position"><?= htmlspecialchars($member['position'] ?? 'Team Member') ?></p>
                                <p class="team-desc"><?= htmlspecialchars($member['description'] ?? '') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align:center;">No team members found.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <span><i class="fa-regular fa-copyright"></i> 2025 Copyright: <b>Alps Electric (Malaysia) Sdn Bhd</b></span>
    </footer>
</div>

<!-- Add & Edit Modals -->
<div id="addTeamMemberModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Add Team Member</div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="addTeamMember">
            <label>Name*</label><input type="text" name="member_name" required>
            <label>Position*</label><input type="text" name="member_position" required>
            <label>Description</label><textarea name="member_description"></textarea>
            <label>Photo*</label><input type="file" name="member_photo" accept="image/*" required>
            <button type="submit" class="btn-action btn-save">Add Member</button>
        </form>
    </div>
</div>

<div id="editTeamMemberModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Edit Team Member</div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="updateTeamMember">
            <input type="hidden" id="editMemberId" name="member_id">
            <label>Name*</label><input type="text" id="editMemberName" name="member_name" required>
            <label>Position*</label><input type="text" id="editMemberPosition" name="member_position" required>
            <label>Description</label><textarea id="editMemberDesc" name="member_description"></textarea>
            <label>New Photo (optional)</label><input type="file" name="member_photo" accept="image/*">
            <button type="submit" class="btn-action btn-save">Update Member</button>
        </form>
    </div>
</div>

<!-- Edit About Section Modal -->
<div id="editAboutModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeEditAboutModal()">&times;</button>
        <div class="modal-header">Edit About Section</div>
        <form method="POST">
            <input type="hidden" name="action" value="updateAbout">
            <div class="form-group">
                <label>Title*</label>
                <input type="text" name="title" value="<?= htmlspecialchars($SQLtitle) ?>" required>
            </div>
            <div class="form-group">
                <label>Description*</label>
                <textarea name="description" required><?= htmlspecialchars($SQLDesc) ?></textarea>
            </div>
            <div class="button-group">
                <button type="submit" class="btn-action btn-save">Save Changes</button>
                <button type="button" class="btn-action btn-cancel" onclick="closeEditAboutModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

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
function openEditAboutModal() {
    document.getElementById('editAboutModal').classList.add('show');
}

function closeEditAboutModal() {
    document.getElementById('editAboutModal').classList.remove('show');
}

function openEditTeamMemberModal(member) {
    document.getElementById('editMemberId').value = member.id;
    document.getElementById('editMemberName').value = member.name;
    document.getElementById('editMemberPosition').value = member.position || '';
    document.getElementById('editMemberDesc').value = member.description || '';
    document.getElementById('editTeamMemberModal').classList.add('show');
}

function closeEditTeamMemberModal() {
    document.getElementById('editTeamMemberModal').classList.remove('show');
}

function openAddTeamMemberModal() {
    document.getElementById('addTeamMemberModal').classList.add('show');
}

function closeAddTeamMemberModal() {
    document.getElementById('addTeamMemberModal').classList.remove('show');
}

function deleteTeamMember(id) {
    if (confirm("Are you sure you want to delete this team member?")) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="deleteTeamMember"><input type="hidden" name="member_id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('show');
    }
}

function openEditTeamMemberModal(member) {
    document.getElementById('editMemberId').value = member.id;
    document.getElementById('editMemberName').value = member.name;
    document.getElementById('editMemberPosition').value = member.position || '';
    document.getElementById('editMemberDesc').value = member.description || '';
    document.getElementById('editTeamMemberModal').classList.add('show');
}

function openAddTeamMemberModal() {
    document.getElementById('addTeamMemberModal').classList.add('show');
}

function deleteTeamMember(id) {
    if (confirm("Are you sure you want to delete this team member?")) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="deleteTeamMember"><input type="hidden" name="member_id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
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

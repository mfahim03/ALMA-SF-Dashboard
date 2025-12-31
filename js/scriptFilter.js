document.addEventListener('DOMContentLoaded', () => {
  // ================== FILTER BUTTONS ==================
  const filterButtons = document.querySelectorAll(".filter-btn");
  const sections = document.querySelectorAll(".section-box");

  filterButtons.forEach(button => {
    button.addEventListener("click", () => {
      filterButtons.forEach(btn => btn.classList.remove("active"));
      button.classList.add("active");

      const filter = button.dataset.filter.toLowerCase();

      sections.forEach(section => {
        const sectionTitle = section.querySelector("h3").textContent.toLowerCase();

        if (filter === "all" || sectionTitle.includes(filter)) {
          section.style.display = "block";
        } else {
          section.style.display = "none";
        }
      });
    });
  });

  // ================== HAMBURGER MENU ==================
  const hamburger = document.getElementById('hamburger');
  const navMenu = document.getElementById('navMenu');

  hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
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

  // ================== LOGOUT BUTTON ==================
  const logoutBtn = document.getElementById('logoutBtn');
  logoutBtn.addEventListener('click', () => {
    if (confirm("Are you sure you want to log out?")) {
      window.location.href = "index.php";
    }
  });

  // ================== PROJECT SEARCH ==================
  searchInput.addEventListener('input', function() {
    const query = this.value.trim().toLowerCase();
    if (!query) { 
      searchResults.classList.remove('active'); 
      return; 
    }

    const filtered = allProjects.filter(p =>
      p.name.toLowerCase().includes(query) || 
      p.type.toLowerCase().includes(query)
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
});

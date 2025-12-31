// Add Modal
  const addModal = document.getElementById("addProjectModal");
  document.getElementById("addProjectBtn").onclick = () => addModal.style.display = "block";
  addModal.querySelector(".close").onclick = () => addModal.style.display = "none";

  // Edit Modal
  const editModal = document.getElementById("editProjectModal");
  document.getElementById("editProjectBtn").onclick = () => editModal.style.display = "block";
  editModal.querySelector(".close-edit").onclick = () => editModal.style.display = "none";

  window.onclick = e => {
    if(e.target===addModal) addModal.style.display="none";
    if(e.target===editModal) editModal.style.display="none";
  };

  // Auto-fill Edit Modal
  const select = document.getElementById("project_id");
  const editName = document.getElementById("edit_name");
  const editIP = document.getElementById("edit_ip");
  const editStatus = document.getElementById("edit_status");
  const editType = document.getElementById("edit_type");
  const currentImagePreview = document.getElementById("current_image_preview");

  function fillEditForm() {
    const opt = select.options[select.selectedIndex];
    editName.value = opt.dataset.name;
    editIP.value = opt.dataset.ip;
    editStatus.value = opt.dataset.status;
    editType.value = opt.dataset.type;
    
    // Display current image
    if (opt.dataset.image) {
      currentImagePreview.src = 'data:image/jpeg;base64,' + opt.dataset.image;
      document.getElementById('current_image_container').style.display = 'block';
    } else {
      document.getElementById('current_image_container').style.display = 'none';
    }
    
    // Reset file input and hide new preview
    document.getElementById('edit_image').value = '';
    document.getElementById('new_image_preview_container').style.display = 'none';
  }
  
  select.addEventListener("change", fillEditForm);
  fillEditForm();
  
  // Preview new image when selected in edit form
  document.getElementById('edit_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        document.getElementById('new_image_preview').src = e.target.result;
        document.getElementById('new_image_preview_container').style.display = 'block';
      };
      reader.readAsDataURL(file);
    } else {
      document.getElementById('new_image_preview_container').style.display = 'none';
    }
  });

  // === Delete Modal ===
document.addEventListener("DOMContentLoaded", () => {
  const deleteModal = document.getElementById("deleteProjectModal");
  const toggleDeleteBtn = document.getElementById("toggleDeleteBtn");
  const deleteForm = document.getElementById("deleteProjectForm");

  if (!deleteModal || !toggleDeleteBtn || !deleteForm) return; // safety check

  // === Toggle show/hide delete modal ===
  toggleDeleteBtn.addEventListener("click", () => {
    deleteModal.classList.toggle("show");
  });

  // === Close modal when clicking "X" button ===
  const closeBtn = deleteModal.querySelector(".close-delete");
  if (closeBtn) {
    closeBtn.addEventListener("click", () => {
      deleteModal.classList.remove("show");
    });
  }

  // === Close modal when clicking outside ===
  window.addEventListener("click", (e) => {
    if (e.target === deleteModal) {
      deleteModal.classList.remove("show");
    }
  });

  // === Confirm before deleting ===
  deleteForm.addEventListener("submit", (e) => {
    const confirmDelete = confirm(
      "Are you sure you want to delete this project? This action cannot be undone."
    );
    if (!confirmDelete) e.preventDefault();
  });
});

document.getElementById('manageCategoryBtn').addEventListener('click', () => {
  document.getElementById('manageCategoryModal').style.display = 'block';
});

document.querySelector('.close-category').addEventListener('click', () => {
  document.getElementById('manageCategoryModal').style.display = 'none';
});
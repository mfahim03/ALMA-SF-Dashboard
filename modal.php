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

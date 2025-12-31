<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $ip = $_POST['ip'];
    $status = $_POST['status'];
    $type = $_POST['type'];
    
    // Check if a new image was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // New image uploaded 
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
        $imageBase64 = base64_encode($imageData);
        
        // Update with new image
        $sql = "UPDATE Projects SET name=?, ip=?, status=?, type=?, image=? WHERE id=?";
        $params = array($name, $ip, $status, $type, $imageBase64, $id);
        
    } else {
        // No new image - update without changing image
        $sql = "UPDATE Projects SET name=?, ip=?, status=?, type=? WHERE id=?";
        $params = array($name, $ip, $status, $type, $id);
    }
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        die("Error updating project: " . print_r(sqlsrv_errors(), true));
    }
    
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    
    // Redirect back to main page
    header("Location: admin.php");
    exit();
} else {
    header("Location: admin.php");
    exit();
}
?>
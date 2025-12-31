<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $name = $_POST['name'];
    $ip = $_POST['ip'];
    $status = $_POST['status'];
    $type = $_POST['type'];
    
    // Check if image was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Image uploaded - process it
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
        $imageBase64 = base64_encode($imageData);
        
        // Insert with image
        $sql = "INSERT INTO Projects (name, ip, status, type, image) VALUES (?, ?, ?, ?, ?)";
        $params = array($name, $ip, $status, $type, $imageBase64);
        
    } else {
        // No image uploaded - insert without image (will be NULL)
        $sql = "INSERT INTO Projects (name, ip, status, type) VALUES (?, ?, ?, ?)";
        $params = array($name, $ip, $status, $type);
    }
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        die("Error adding project: " . print_r(sqlsrv_errors(), true));
    }
    
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    
    // Redirect back to main page
    header("Location: index.php");
    exit();
} else {
    header("Location: admin.php");
    exit();
}
?>
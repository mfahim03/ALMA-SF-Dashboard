<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    $sql = "DELETE FROM Projects WHERE id = ?";
    $stmt = sqlsrv_query($conn, $sql, [$id]);

    if ($stmt) {
        sqlsrv_free_stmt($stmt);
        echo "<script>alert('Project deleted successfully!'); window.location.href='admin.php';</script>";
    } else {
        echo "<script>alert('Error deleting project.'); window.location.href='admin.php';</script>";
    }

    sqlsrv_close($conn);
} else {
    header("Location: index.php");
    exit;
}
?>

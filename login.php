<?php
session_start();
include 'db.php';

// ================== HANDLE LOGIN ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        $params = [$username];
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            // Log error to server log (not shown to user)
            error_log("Database error: " . print_r(sqlsrv_errors(), true));
            $_SESSION['login_error'] = 'Internal server error.';
            header('Location: index.php?login_error=1');
            exit;
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($stmt);

        if ($row) {
            $dbPass = $row['password'];

            // ✅ Password check (works for both hashed or plain)
            if (password_verify($password, $dbPass) || $password === $dbPass) {
                // ✅ Regenerate session ID for security
                session_regenerate_id(true);

                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['logged_in'] = true;

                // ✅ Only 1 admin
                $_SESSION['is_admin'] = true;

                header('Location: admin.php');
                exit;
            } else {
                $_SESSION['login_error'] = 'Incorrect password.';
            }
        } else {
            $_SESSION['login_error'] = 'User not found.';
        }
    } else {
        $_SESSION['login_error'] = 'Please fill in both fields.';
    }

    header('Location: index.php?login_error=1');
    exit;
}
?>

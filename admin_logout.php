<?php
// admin_logout.php
session_start();

// Only admins reach here; but even if not, we just clear and send to auth.
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// (Optional) start a new session just to carry a flash message
session_start();
$_SESSION['flash'] = 'You have been logged out.';

header("Location: /My_Project/auth.php");
exit;
?>
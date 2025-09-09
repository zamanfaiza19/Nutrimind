<?php
declare(strict_types=1);
session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once __DIR__ . '/dbconnect.php';

//login as admin
if (empty($_SESSION['is_admin']) || (int)$_SESSION['is_admin'] !== 1) {
    header('Location: /My_Project/auth.php');
    exit;
}

$adminName = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');

$counts = ['members' => 0, 'music' => 0];
try {
    if (isset($conn) && $conn instanceof mysqli) {
        $r = $conn->query("SELECT COUNT(*) c FROM member");
        $counts['members'] = (int)($r->fetch_assoc()['c'] ?? 0);

        $r = $conn->query("SELECT COUNT(*) c FROM Music");
        $counts['music'] = (int)($r->fetch_assoc()['c'] ?? 0);
    }
} catch (Throwable $e) {
    $counts['note'] = 'Info counts unavailable: '. $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Nutrimind — Admin Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    :root{--bg:#fecece;--card:#ffffff;--ink:#1a1a1a;--muted:#6b7280;--brand:#e07a88}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;background:linear-gradient(135deg,#efd6d6,#e5a2a2);min-height:100vh;display:flex;align-items:center;justify-content:center}
    .card{width:min(900px,96vw);background:var(--card);border-radius:18px;box-shadow:0 18px 60px rgba(0,0,0,.15);padding:22px}
    h1{margin:0 0 6px;color:var(--ink)}
    .sub{color:var(--muted);margin:0 0 18px}
    .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;margin-top:14px}
    a.btn{display:block;text-decoration:none;background:#ffa3a3;color:#111;padding:14px;border-radius:12px;font-weight:600;text-align:center;border:1px solid #efd1d1}
    a.btn:hover{background:#ffbaba}
    .stats{display:flex;gap:16px;margin-top:14px;color:#333}
    .stat{background:#ff9797;border:1px solid rgb(252, 252, 252); border-radius:12px;padding:10px 14px}
    .logout{display:inline-block;margin-top:18px;color:#b61f3b;text-decoration:none}
  </style>
</head>
<body>
  <div class="card">
    <h1>Admin Dashboard</h1>
    <p class="sub">Welcome, <b><?php echo $adminName; ?></b> 👋</p>

    <div class="stats">
      <div class="stat">Members: <b><?php echo (int)$counts['members']; ?></b></div>
      <div class="stat">Songs: <b><?php echo (int)$counts['music']; ?></b></div>
    </div>
    <?php if (!empty($counts['note'])): ?>
      <p class="sub" style="margin-top:6px;"><?php echo htmlspecialchars($counts['note']); ?></p>
    <?php endif; ?>

    <div class="grid" style="margin-top:16px">
      <a class="btn" href="/My_Project/admin_music.php">➕ Add Music</a>
      <a class="btn" href="/My_Project/admin_meal.php">🍽️ Add Meal</a>
      <a class="btn" href="/My_Project/admin_users.php">👥 Manage Users</a>
      <a class="btn" href="/My_Project/admin_playlists.php">🎵 Playlists</a>
      <a class="btn" href="/My_Project/admin_logout.php">🚪 Logout</a>
    </div>
</body>
</html>

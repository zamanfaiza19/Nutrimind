<?php
session_start();
require_once __DIR__ . '/dbconnect.php';
if (empty($_SESSION['is_admin']) || (int)$_SESSION['is_admin'] !== 1) {
    header("Location: /My_Project/auth.php");
    exit;
}
function s($v){ return trim((string)$v); }

$flash = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['block'], $_POST['member_id'])) {
    $mid = (int)$_POST['member_id'];
    if ($mid > 0) {
        if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $mid) {
            $flash = "❌ You cannot block yourself.";
        } else {
            //already blocked
            $check = $conn->prepare("SELECT 1 FROM blocked_users WHERE Member_id = ? LIMIT 1");
            if ($check) {
                $check->bind_param("i", $mid);
                $check->execute();
                $exists = $check->get_result()->fetch_row();
                $check->close();
            } else {
                $exists = false;
            }

            if ($exists) {
                $flash = "ℹ️ User is already blocked.";
            } else {
                $stmt = $conn->prepare("INSERT INTO blocked_users (Member_id) VALUES (?)");
                if ($stmt) {
                    $stmt->bind_param("i", $mid);
                    if ($stmt->execute()) {
                        $flash = "🔒 User #$mid blocked.";
                    } else {
                        $flash = "❌ Could not block user: ".htmlspecialchars($stmt->error);
                    }
                    $stmt->close();
                } else {
                    $flash = "❌ DB prepare error when blocking.";
                }
            }
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unblock'], $_POST['member_id'])) {
    $mid = (int)$_POST['member_id'];
    if ($mid > 0) {
        $stmt = $conn->prepare("DELETE FROM blocked_users WHERE Member_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $mid);
            if ($stmt->execute()) {
                $flash = "🔓 User #$mid unblocked.";
            } else {
                $flash = "❌ Could not unblock user: ".htmlspecialchars($stmt->error);
            }
            $stmt->close();
        } else {
            $flash = "❌ DB prepare error when unblocking.";
        }
    }
}

// DELETE a user 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'], $_POST['member_id'])) {
    $mid = (int)$_POST['member_id'];
    if ($mid > 0) {

        $conn->query("DELETE FROM blocked_users WHERE Member_id = ".(int)$mid);
        $stmt = $conn->prepare("DELETE FROM member WHERE Member_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $mid);
            if ($stmt->execute()) {

                if ($stmt->affected_rows > 0) {
                    $flash = "🗑️ Deleted user #$mid.";
                } else {
                    $flash = "ℹ️ No user deleted (not found).";
                }
            } else {
                $flash = "❌ Could not delete user: ".htmlspecialchars($stmt->error);
            }
            $stmt->close();
        } else {
            $flash = "❌ DB prepare error when deleting.";
        }
    }
}

$sql = "
SELECT
  m.Member_id,
  m.First_name,
  m.Last_name,
  m.Email,
  m.Gender,
  m.Height_cm,
  m.Weight_kg,
  CASE WHEN b.Member_id IS NULL THEN 0 ELSE 1 END AS is_blocked
FROM member m
LEFT JOIN blocked_users b ON b.Member_id = m.Member_id
ORDER BY m.Member_id ASC
";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin — Manage Users</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
  :root{
    --bg:#f6dfe5; --card:#fff; --ink:#222; --muted:#666; --accent:#e48fa0;
    --ok:#28a745; --warn:#dc3545; --link:#ff9f9f;
  }
  body{margin:0;font-family:-apple-system,Segoe UI,Inter,Arial;background:var(--bg);}
  .wrap{max-width:1100px;margin:28px auto;padding:0 14px}
  .card{background:var(--card);border-radius:14px;box-shadow:0 12px 30px rgba(0,0,0,.08);padding:20px}
  h1{margin:0 0 12px}
  .flash{margin:12px 0;padding:10px 12px;border-radius:10px;background:#f0f7f2;border:1px solid #cfe8d5}
  table{width:100%;border-collapse:collapse;margin-top:10px}
  th,td{border-bottom:1px solid #eee;padding:10px;text-align:left}
  th{background:#faf7f8}
  .badge{padding:3px 8px;border-radius:999px;font-size:.85rem;border:1px solid #ddd}
  .blocked{background:#ffe6e6;color:#a30000;border-color:#ffb3b3}
  .active{background:#e9fff0;color:#0a6b2d;border-color:#bde5c8}
  form.inline{display:inline}
  button{border:none;border-radius:8px;padding:8px 10px;cursor:pointer;font-weight:600}
  .btn-block{background:#ffb84d}
  .btn-unblock{background:#52e775;color:#fff}
  .btn-del{background:#dc3545;color:#fff}
  .back{display:inline-block;margin-top:12px;text-decoration:none;background:var(--link);color:#fff;padding:9px 12px;border-radius:8px}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <h1>👥 Manage Users</h1>

    <?php if (!empty($flash)): ?>
      <div class="flash"><?= $flash ?></div>
    <?php endif; ?>

    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Gender</th>
          <th>Ht (cm)</th>
          <th>Wt (kg)</th>
          <th>Status</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($res && $res->num_rows > 0) {
            $i=1;
            while ($u = $res->fetch_assoc()) {
                $id = (int)$u['Member_id'];
                $name = htmlspecialchars($u['First_name'].' '.$u['Last_name']);
                $email = htmlspecialchars($u['Email']);
                $gender = htmlspecialchars($u['Gender']);
                $h = (int)$u['Height_cm'];
                $w = (int)$u['Weight_kg'];
                $blocked = (int)$u['is_blocked'] === 1;
                echo "<tr>";
                echo "<td>".($i++)."</td>";
                echo "<td>$name</td>";
                echo "<td>$email</td>";
                echo "<td>$gender</td>";
                echo "<td>$h</td>";
                echo "<td>$w</td>";
                echo "<td>";
                echo $blocked
                    ? "<span class='badge blocked'>Blocked</span>"
                    : "<span class='badge active'>Active</span>";
                echo "</td>";
                echo "<td style='text-align:right'>";
                if ($blocked) {
                    echo "<form method='post' class='inline'>
                            <input type='hidden' name='member_id' value='$id'>
                            <button type='submit' name='unblock' class='btn-unblock'>Unblock</button>
                          </form> ";
                } else {
                    echo "<form method='post' class='inline'>
                            <input type='hidden' name='member_id' value='$id'>
                            <button type='submit' name='block' class='btn-block'>Block</button>
                          </form> ";
                }
                echo "<form method='post' class='inline' onsubmit=\"return confirm('Delete user #$id and their data? This cannot be undone.');\">
                        <input type='hidden' name='member_id' value='$id'>
                        <button type='submit' name='delete' class='btn-del'>Delete</button>
                      </form>";
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='8'>No users found.</td></tr>";
        }
        ?>
      </tbody>
    </table>

    <a class="back" href="admin_home.php">⬅ Back to Admin Home</a>
  </div>
</div>
</body>
</html>

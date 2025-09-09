<?php
session_start();
require_once __DIR__ . "/dbconnect.php";
if (empty($_SESSION["is_admin"]) || (int)$_SESSION["is_admin"] !== 1) {
    header("Location: /My_Project/auth.php");
    exit;
}

$flash = "";

// Delete Playlist
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete"], $_POST["playlist_id"])) {
    $id = (int)$_POST["playlist_id"];
    if ($id > 0) {
        $conn->query("DELETE FROM added_to WHERE playlist_id = $id");
        $stmt = $conn->prepare("DELETE FROM Playlist WHERE Playlist_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $flash = "🗑️ Deleted playlist (ID: $id)";
            } else {
                $flash = "❌ Delete failed: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$result = $conn->query("SELECT Playlist_id, Playlist_name FROM Playlist ORDER BY Playlist_id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin — Manage Playlists</title>
<style>
  body{font-family:Arial;background:#ffc8c8;padding:20px;}
  .card{background:#fdf9f9;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.1);max-width:800px;margin:auto;}
  h1{margin-top:0;}
  .flash{padding:10px;margin-bottom:10px;background:#e7f8e7;border:1px solid #b5e3b5;border-radius:6px;}
  table{width:100%;border-collapse:collapse;margin-top:10px;}
  th,td{padding:10px;border-bottom:1px solid #ffbcbc;text-align:left;}
  .btn{padding:6px 12px;border:none;border-radius:6px;cursor:pointer;}
  .btn-del{background:#ef6969;color:#fff;}
  .btn-back{background:#fe7070;color:#fff;text-decoration:none;padding:8px 14px;border-radius:6px;}
</style>
</head>
<body>
<div class="card">
  <h1>📂 Manage Playlists</h1>

  <?php if ($flash): ?>
    <div class="flash"><?= htmlspecialchars($flash) ?></div>
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Playlist Name</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if ($result && $result->num_rows > 0) {
          $serial = 1;
          while ($row = $result->fetch_assoc()) {
              echo "<tr>";
              echo "<td>" . $serial++ . "</td>";
              echo "<td>" . htmlspecialchars($row["Playlist_name"]) . "</td>";
              echo "<td>
                      <form method='post' onsubmit=\"return confirm('Delete this playlist?');\">
                        <input type='hidden' name='playlist_id' value='".$row["Playlist_id"]."'>
                        <button type='submit' name='delete' class='btn btn-del'>🗑 Delete</button>
                      </form>
                    </td>";
              echo "</tr>";
          }
      } else {
          echo "<tr><td colspan='3'>No playlists found.</td></tr>";
      }
      ?>
    </tbody>
  </table>
  <br>
  <a href="admin_home.php" class="btn-back">⬅ Back</a>
</div>
</body>
</html>

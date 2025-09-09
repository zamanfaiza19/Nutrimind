<?php
session_start();
require_once __DIR__ . "/dbconnect.php";
$flash = ""; 

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES); }
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
function pexec(mysqli $conn, string $sql, string $types = "", array $params = []) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) return [false, "Prepare failed: ".$conn->error, null];
    if ($types !== "" && $params) $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) { $err = $stmt->error; $stmt->close(); return [false, "Execute failed: ".$err, null]; }
    $res = null;
    if (method_exists($stmt, 'get_result')) $res = $stmt->get_result();
    return [true, "", [$stmt, $res]];
}
function fetch_all_assoc(mysqli_stmt $stmt, $resOrNull) {
    if ($resOrNull instanceof mysqli_result) {
        $rows = [];
        while ($r = $resOrNull->fetch_assoc()) $rows[] = $r;
        $closer = function() use ($stmt, $resOrNull) { $resOrNull->free(); $stmt->close(); };
        return [$rows, $closer];
    }
    $meta = $stmt->result_metadata();
    if (!$meta) { $closer = function() use ($stmt) { $stmt->close(); }; return [[], $closer]; }
    $row = []; $refs = [];
    while ($f = $meta->fetch_field()) { $row[$f->name] = null; $refs[] =& $row[$f->name]; }
    $stmt->store_result();
    call_user_func_array([$stmt,'bind_result'], $refs);
    $rows = [];
    while ($stmt->fetch()) { $copy=[]; foreach ($row as $k=>$v) $copy[$k]=$v; $rows[]=$copy; }
    $stmt->free_result();
    $closer = function() use ($stmt) { $stmt->close(); };
    return [$rows, $closer];
}

$PUBLIC_BASE = '/uploads/music';                 
$FILES_DIR   = __DIR__ . $PUBLIC_BASE;      
if (!is_dir($FILES_DIR)) {
  @mkdir($FILES_DIR, 0775, true);
}
if (is_dir($FILES_DIR) && !is_writable($FILES_DIR)) {
  @chmod($FILES_DIR, 0775);
}

function filename_to_title(string $fn): string {
    $t = pathinfo($fn, PATHINFO_FILENAME);
    $t = preg_replace('/[_%-]+/', ' ', $t);
    $t = preg_replace('/\s+/', ' ', $t);
    return trim($t);
}

function safe_target_path(string $dir, string $origBase): array {
    $ext = strtolower(pathinfo($origBase, PATHINFO_EXTENSION));
    if ($ext !== 'mp3') return [null, "Only .mp3 is allowed"];
    $name = preg_replace('/[^A-Za-z0-9._-]+/', '-', basename($origBase)); 
    $name = preg_replace('/-+/', '-', $name);
    if ($name === '' || $name[0] === '.') $name = 'track.mp3';
    $dest = $dir . '/' . $name;
    $i = 1;
    while (file_exists($dest)) {
        $baseNoExt = pathinfo($name, PATHINFO_FILENAME);
        $dest = $dir . '/' . $baseNoExt . "-$i.mp3";
        $i++;
        if ($i > 5000) return [null, "Filename conflict loop"];
    }
    return [$dest, null];
}
$conn->query("ALTER TABLE `Music` ADD COLUMN IF NOT EXISTS `file_path` VARCHAR(255) NOT NULL AFTER `Music name`");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action']) && $_POST['action'] === 'add_one') {
        $title  = trim($_POST['title'] ?? "");
        $mood   = trim($_POST['mood'] ?? "unknown");
        $artist = trim($_POST['artist'] ?? "");
        $mtype  = 'mp3';
        $type   = 'audio/mpeg';
        $file_path = "";

        if (!empty($_FILES['song']['tmp_name']) && is_uploaded_file($_FILES['song']['tmp_name'])) {
            if (!is_dir($FILES_DIR)) @mkdir($FILES_DIR, 0775, true);
            if (!is_dir($FILES_DIR)) {
                $flash = "❌ Upload folder missing: ".h($FILES_DIR);
            } else {
                [$dest, $errName] = safe_target_path($FILES_DIR, $_FILES['song']['name']);
                if ($errName) {
                    $flash = "❌ $errName";
                } else {
                    if (!@move_uploaded_file($_FILES['song']['tmp_name'], $dest)) {
                        $flash = "❌ Unable to move uploaded file to: ".h($dest);
                    } else {
                        $base = basename($dest);
                        $file_path = $PUBLIC_BASE . '/' . $base;
                        if ($title === "") $title = filename_to_title($base);
                    }
                }
            }
        }

        if ($file_path === "") {
            $file_path = trim($_POST['file_path'] ?? "");
        }

        if ($title === "") $title = "Untitled";
        if ($file_path === "") {
            $flash = "❌ Provide an MP3 file or a valid file_path.";
        } else {
            $sql = "INSERT INTO `Music` (`Music name`, `mood`, `artist_name`, `type`, `Music_type`, `file_path`)
                    SELECT ?, ?, ?, ?, ?, ?
                    FROM DUAL
                    WHERE NOT EXISTS (SELECT 1 FROM `Music` WHERE `file_path` = ?)";
            [$ok,$err,$out] = pexec($conn, $sql, "sssssss", [$title,$mood,$artist,$type,$mtype,$file_path,$file_path]);
            if (!$ok) $flash = "❌ Insert failed: ".h($err);
            else {
                if ($conn->affected_rows > 0) $flash = "✅ Added: ".h($title);
                else $flash = "ℹ️ Skipped (already exists): ".h($file_path);
                if ($out && $out[0]) $out[0]->close();
            }
        }
    }

    // EDIT song
    if (isset($_POST['action']) && $_POST['action'] === 'update' && isset($_POST['music_id'])) {
        $id     = (int)$_POST['music_id'];
        $title  = trim($_POST['title'] ?? "");
        $mood   = trim($_POST['mood'] ?? "unknown");
        $artist = trim($_POST['artist'] ?? "");
        $type   = trim($_POST['type'] ?? "audio/mpeg");
        $mtype  = trim($_POST['mtype'] ?? "mp3");
        $file_path = trim($_POST['file_path'] ?? "");

        if ($title === "") $title = "Untitled";
        if ($file_path === "") { $flash = "❌ file_path required."; }
        else {
            $sql = "UPDATE `Music`
                    SET `Music name`=?, `mood`=?, `artist_name`=?, `type`=?, `Music_type`=?, `file_path`=?
                    WHERE `music_id`=?";
            [$ok,$err,$out] = pexec($conn, $sql, "ssssssi", [$title,$mood,$artist,$type,$mtype,$file_path,$id]);
            if (!$ok) $flash = "❌ Update failed: ".h($err);
            else { $flash = "✅ Updated #$id"; if ($out && $out[0]) $out[0]->close(); }
        }
    }

    // DELETE song
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['music_id'])) {
        $id = (int)$_POST['music_id'];
    
        pexec($conn, "DELETE FROM `added_to` WHERE `music_id`=?", "i", [$id]);
        [$ok,$err,$out] = pexec($conn, "DELETE FROM `Music` WHERE `music_id`=?", "i", [$id]);
        if (!$ok) $flash = "❌ Delete failed: ".h($err);
        else { $flash = "🗑️ Deleted #$id"; if ($out && $out[0]) $out[0]->close(); }
    }
    if (isset($_POST['action']) && $_POST['action'] === 'bulk_scan') {
        if (!is_dir($FILES_DIR)) @mkdir($FILES_DIR, 0775, true);
        if (!is_dir($FILES_DIR)) {
            $flash = "❌ Folder missing: ".h($FILES_DIR);
        } else {
            $files = glob($FILES_DIR.'/*.mp3');
            $added=0; $skipped=0;
            $sql = "INSERT INTO `Music` (`Music name`, `mood`, `artist_name`, `type`, `Music_type`, `file_path`)
                    SELECT ?, 'unknown', '', 'audio/mpeg', 'mp3', ?
                    FROM DUAL
                    WHERE NOT EXISTS (SELECT 1 FROM `Music` WHERE `file_path` = ?)";
            foreach ($files as $full) {
                $base = basename($full);
                $title = filename_to_title($base);
                $path  = $PUBLIC_BASE . '/' . $base;
                [$ok,$err,$out] = pexec($conn, $sql, "sss", [$title,$path,$path]);
                if ($ok) {
                    if ($conn->affected_rows > 0) $added++; else $skipped++;
                    if ($out && $out[0]) $out[0]->close();
                }
            }
            $flash = "✅ Bulk scan finished. Added: $added, Skipped: $skipped";
        }
    }
}

// -------------------- GET (list & edit) --------------------
$search = trim($_GET['q'] ?? "");
$mood   = trim($_GET['mood'] ?? "");
$page   = max(1, (int)($_GET['p'] ?? 1));
$limit  = 20;
$offset = ($page - 1) * $limit;

// no "Epic" per your request
$MOODS = ["Happy","Sad","Romantic","Energetic","Chill","Workout","Sleep"];

// single record for edit if requested
$editing = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    [$ok,$err,$out] = pexec($conn,
        "SELECT music_id, `Music name` AS title, mood, artist_name, type, Music_type, file_path
         FROM `Music` WHERE `music_id`=?",
        "i", [$id]
    );
    if ($ok) { [$rows,$c] = fetch_all_assoc($out[0], $out[1]); $c(); if ($rows) $editing = $rows[0]; }
}

// list query
$where = "1=1";
$params = []; $types = "";
if ($search !== "") {
    $where .= " AND (LOWER(`Music name`) LIKE CONCAT('%',LOWER(?),'%')
                OR LOWER(`artist_name`) LIKE CONCAT('%',LOWER(?),'%'))";
    $params[]=$search; $params[]=$search; $types.="ss";
}
if ($mood !== "") {
    $where .= " AND LOWER(`mood`) LIKE CONCAT('%',LOWER(?),'%')";
    $params[]=$mood; $types.="s";
}

$count_sql = "SELECT COUNT(*) AS c FROM `Music` WHERE $where";
[$okc,$errc,$outc] = pexec($conn, $count_sql, $types, $params);
$total = 0;
if ($okc) { [$rows,$cc] = fetch_all_assoc($outc[0], $outc[1]); $cc(); $total = $rows ? (int)$rows[0]['c'] : 0; }

$list_sql = "SELECT music_id, `Music name` AS title, mood, artist_name, type, Music_type, file_path
             FROM `Music` WHERE $where
             ORDER BY music_id ASC
             LIMIT ? OFFSET ?";
$params2 = $params; $types2 = $types . "ii";
$params2[] = $limit; $params2[] = $offset;
[$okl,$errl,$outl] = pexec($conn, $list_sql, $types2, $params2);
$songs = [];
if ($okl) { [$songs,$cl] = fetch_all_assoc($outl[0], $outl[1]); $cl(); }

$pages = max(1, (int)ceil($total / $limit));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Admin · Music</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
  :root{ --bg:#f6e6ea; --card:#fff; --ink:#222; --muted:#6b7280; --pill:#f1f5f9; --accent:#e68aa0;}
  body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Inter,Arial;background:var(--bg);color:var(--ink);}
  .wrap{max-width:1100px;margin:24px auto;padding:0 16px}
  h1{margin:0 0 12px}
  .card{background:#fff;border-radius:14px;box-shadow:0 10px 26px rgba(0,0,0,.08);padding:16px;margin:12px 0}
  .toolbar{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
  input[type=text], select{padding:10px;border:1px solid #e5e7eb;border-radius:8px}
  .btn{background:var(--accent);color:#fff;border:none;border-radius:8px;padding:8px 12px;font-weight:600;cursor:pointer}
  .btn:hover{filter:brightness(1.05)}
  .btn-danger{background:#ef8a8a}
  table{width:100%;border-collapse:collapse}
  th,td{border-bottom:1px solid #eee;padding:8px;text-align:left;vertical-align:top}
  audio{width:220px}
  .muted{color:#6b7280}
  .flash{background:#fde2e4;border:1px solid #f6c2ca;padding:10px;border-radius:10px}
  .ok{background:#ecfdf5;border:1px solid #bbf7d0}
  .grid2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .right{margin-left:auto}
  .pagination a{padding:6px 10px;border:1px solid #ccc;border-radius:8px;background:#fff;text-decoration:none;color:#111}
  .pagination .current{padding:6px 10px;border-radius:8px;background:#111;color:#fff}
</style>
</head>
<body>
<div class="wrap">

  <!-- NEW: Back to Admin Home button -->
  <div style="margin-bottom:8px">
    <a class="btn" href="admin_home.php">← Back to Admin Home</a>
  </div>

  <h1>🎶 Admin · Music</h1>

  <?php if ($flash): ?><div class="card flash"><?= $flash ?></div><?php endif; ?>

  <!-- Search / Filters (Bulk button removed) -->
  <div class="card">
    <form method="get" class="toolbar">
      <input type="text" name="q" placeholder="Search title or artist..." value="<?= h($search) ?>">
      <select name="mood">
        <option value="">All moods</option>
        <?php foreach ($MOODS as $m): ?>
          <option value="<?= h($m) ?>" <?= $m===$mood?'selected':'' ?>><?= h($m) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn" type="submit">Apply</button>
      <a class="btn" href="admin_music.php" style="text-decoration:none">Clear</a>
    </form>
  </div>

  <!-- Add One -->
  <div class="card">
    <h3>Add song</h3>
    <form method="post" enctype="multipart/form-data" class="grid2">
      <div>
        <label>Upload MP3 (optional)</label><br>
        <input type="file" name="song" accept=".mp3,audio/mpeg">
        <div class="muted">Or leave empty and provide a <code>file_path</code> below.</div>
      </div>
      <div>
        <label>file_path (e.g., <?= h($PUBLIC_BASE) ?>/Track.mp3)</label><br>
        <input type="text" name="file_path" placeholder="<?= h($PUBLIC_BASE) ?>/file.mp3">
      </div>
      <div>
        <label>Title</label><br>
        <input type="text" name="title" placeholder="Song title">
      </div>
      <div>
        <label>Mood</label><br>
        <select name="mood">
          <?php foreach ($MOODS as $m): ?>
            <option value="<?= h($m) ?>"><?= h($m) ?></option>
          <?php endforeach; ?>
          <option value="unknown">unknown</option>
        </select>
      </div>
      <div>
        <label>Artist</label><br>
        <input type="text" name="artist" placeholder="Artist name">
      </div>
      <div style="display:flex;align-items:flex-end">
        <input type="hidden" name="action" value="add_one">
        <button class="btn" type="submit">Add</button>
      </div>
    </form>
  </div>

  <!-- Edit -->
  <?php if ($editing): ?>
    <div class="card">
      <h3>Edit #<?= (int)$editing['music_id'] ?></h3>
      <form method="post" class="grid2">
        <div>
          <label>Title</label><br>
          <input type="text" name="title" value="<?= h($editing['title']) ?>">
        </div>
        <div>
          <label>Mood</label><br>
          <input type="text" name="mood" value="<?= h($editing['mood']) ?>">
        </div>
        <div>
          <label>Artist</label><br>
          <input type="text" name="artist" value"><?= h($editing['artist_name']) ?>">
        </div>
        <div>
          <label>file_path</label><br>
          <input type="text" name="file_path" value="<?= h($editing['file_path']) ?>">
        </div>
        <div>
          <label>type</label><br>
          <input type="text" name="type" value="<?= h($editing['type']) ?>">
        </div>
        <div>
          <label>Music_type</label><br>
          <input type="text" name="mtype" value="<?= h($editing['Music_type']) ?>">
        </div>
        <div style="display:flex;gap:8px;align-items:flex-end">
          <input type="hidden" name="music_id" value="<?= (int)$editing['music_id'] ?>">
          <input type="hidden" name="action" value="update">
          <button class="btn" type="submit">Save</button>
          <a class="btn" href="admin_music.php" style="text-decoration:none">Cancel</a>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <!-- List -->
  <div class="card">
    <h3>All songs (<?= (int)$total ?>)</h3>
    <?php if (!$songs): ?>
      <p class="muted">No songs found.</p>
    <?php else: ?>
      <?php $serial = $offset + 1; ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Title / Artist</th>
            <th>Mood</th>
            <th>Play</th>
            <th>Path</th>
            <th style="width:160px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($songs as $s): ?>
            <tr>
              <td><?= $serial++ ?></td>
              <td>
                <strong><?= h($s['title']) ?></strong><br>
                <span class="muted"><?= h($s['artist_name']) ?></span>
              </td>
              <td><?= h($s['mood']) ?></td>
              <td>
                <?php if (!empty($s['file_path'])): ?>
                  <audio controls preload="none">
                    <source src="<?= h($s['file_path']) ?>" type="audio/mpeg">
                  </audio>
                <?php endif; ?>
              </td>
              <td class="muted" style="max-width:260px;word-break:break-all"><?= h($s['file_path']) ?></td>
              <td>
                <a class="btn" href="admin_music.php?edit=<?= (int)$s['music_id'] ?>">Edit</a>
                <form method="post" style="display:inline" onsubmit="return confirm('Delete this song? This will also remove it from playlists.');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="music_id" value="<?= (int)$s['music_id'] ?>">
                  <button class="btn btn-danger" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <?php if ($pages > 1): ?>
        <div class="pagination" style="margin-top:12px;display:flex;gap:6px;flex-wrap:wrap">
          <?php for ($i=1; $i<=$pages; $i++): 
               $qs = http_build_query(['q'=>$search,'mood'=>$mood,'p'=>$i]); ?>
            <?php if ($i === $page): ?>
              <span class="current"><?= $i ?></span>
            <?php else: ?>
              <a href="?<?= $qs ?>"><?= $i ?></a>
            <?php endif; ?>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
</body>
</html>

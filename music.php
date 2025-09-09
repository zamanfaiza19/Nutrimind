<?php
session_start();
require_once __DIR__ . "/dbconnect.php";

if (empty($_SESSION['user_id'])) {
    header("Location: /My_Project/auth.php");
    exit;
}

$user_id   = (int)$_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['user_name'] ?? 'User', ENT_QUOTES);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES); }

$moodEmojis = [
    "Happy"     => "😊",
    "Sad"       => "😢",
    "Romantic"  => "💖",
    "Energetic" => "⚡",
    "Chill"     => "😎",
    "Workout"   => "🏋️",
    "Sleep"     => "😴",
];
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

$flash = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_empty'])) {
        $name  = trim($_POST['empty_name'] ?? "");
        if ($name === "") $name = "My Playlist";

        [$ok,$err,$out] = pexec($conn, "INSERT INTO Playlist (Playlist_name) VALUES (?)", "s", [$name]);
        if (!$ok) $flash = "❌ Could not create playlist: ".h($err);
        else { $out[0]->close(); $flash = "✅ Playlist '".h($name)."' created."; }
    }

    // Create mood playlist
    if (isset($_POST['create_mood'])) {
        $mood  = trim($_POST['mood_pick'] ?? "");
        $name  = trim($_POST['mood_name'] ?? "");

        if ($mood === "") {
            $flash = "❌ Please choose a mood.";
        } else {
            if ($name === "") $name = $mood." Mix";

            [$ok,$err,$out] = pexec($conn, "INSERT INTO Playlist (Playlist_name) VALUES (?)", "s", [$name]);
            if (!$ok) {
                $flash = "❌ Could not create playlist: ".h($err);
            } else {
                $plid = $conn->insert_id;
                $out[0]->close();

                $sql = "INSERT INTO added_to (Member_id, music_id, playlist_id)
                        SELECT ?, m.music_id, ?
                        FROM Music m
                        WHERE LOWER(m.mood) LIKE CONCAT('%', LOWER(?), '%')";
                [$ok2,$err2,$out2] = pexec($conn, $sql, "iis", [$user_id, $plid, $mood]);
                if (!$ok2) $flash = "⚠️ Playlist '".h($name)."' created, but adding songs failed: ".h($err2);
                else       $flash = "✅ Playlist '".h($name)."' created from mood '".h($mood)."'!";
                if ($out2 && $out2[0]) $out2[0]->close();
            }
        }
    }

    // Add a song to playlist
    if (isset($_POST['add_one']) && isset($_POST['music_id']) && isset($_POST['playlist_id'])) {
        $mid = (int)$_POST['music_id']; $pid = (int)$_POST['playlist_id'];
        [$ok0,$err0,$out0] = pexec($conn, "SELECT 1 FROM added_to WHERE Member_id=? AND music_id=? AND playlist_id=? LIMIT 1", "iii", [$user_id,$mid,$pid]);
        if ($ok0) {
            [$exist,$close0] = fetch_all_assoc($out0[0], $out0[1]); $close0();
            if ($exist) $flash = "ℹ️ That song is already in the playlist.";
            else {
                [$ok,$err,$out] = pexec($conn, "INSERT INTO added_to (Member_id, music_id, playlist_id) VALUES (?,?,?)", "iii", [$user_id,$mid,$pid]);
                if (!$ok) $flash = "❌ Could not add song: ".h($err); else $flash = "✅ Song added to playlist.";
                if ($out && $out[0]) $out[0]->close();
            }
        } else $flash = "❌ Check failed: ".h($err0);
    }

    // Delete playlist 
    if (isset($_POST['delete_pl']) && isset($_POST['playlist_id'])) {
        $pid = (int)$_POST['playlist_id'];
        [$ok,$err,$out] = pexec($conn, "DELETE FROM added_to WHERE Member_id=? AND playlist_id=?", "ii", [$user_id,$pid]);
        if (!$ok) $flash = "❌ Could not delete your playlist entries: ".h($err);
        else      $flash = "🗑️ Playlist removed from your library.";
        if ($out && $out[0]) $out[0]->close();
    }
}

$mood = trim($_GET['mood'] ?? "");
$view_pid = isset($_GET['view']) ? (int)$_GET['view'] : 0;

$pls = [];
[$ok,$err,$out] = pexec($conn,
    "SELECT p.Playlist_id, p.Playlist_name, COUNT(a.music_id) AS tracks
     FROM Playlist p
     JOIN added_to a ON a.playlist_id = p.Playlist_id
     WHERE a.Member_id=?
     GROUP BY p.Playlist_id, p.Playlist_name
     ORDER BY p.Playlist_id DESC",
     "i", [$user_id]
);
if ($ok) { [$pls,$close] = fetch_all_assoc($out[0], $out[1]); $close(); }

// Songs for mood 
$suggest = []; $all_mood = [];
if ($mood !== "") {
    [$ok1,$err1,$out1] = pexec($conn,
        "SELECT music_id, `Music name` AS title, artist_name, mood, file_path
         FROM Music
         WHERE LOWER(mood) LIKE CONCAT('%', LOWER(?), '%')
         ORDER BY RAND()
         LIMIT 10",
        "s", [$mood]
    );
    if ($ok1) { [$suggest,$c1] = fetch_all_assoc($out1[0], $out1[1]); $c1(); }

    [$ok2,$err2,$out2] = pexec($conn,
        "SELECT music_id, `Music name` AS title, artist_name, mood, type, Music_type, file_path
         FROM Music
         WHERE LOWER(mood) LIKE CONCAT('%', LOWER(?), '%')
         ORDER BY music_id DESC",
        "s", [$mood]
    );
    if ($ok2) { [$all_mood,$c2] = fetch_all_assoc($out2[0], $out2[1]); $c2(); }
}
//track playlist
$playlist_view = null; $playlist_tracks = [];
if ($view_pid > 0) {
    [$okv,$errv,$outv] = pexec($conn,
        "SELECT p.Playlist_id, p.Playlist_name
         FROM Playlist p
         JOIN added_to a ON a.playlist_id = p.Playlist_id
         WHERE a.Member_id=? AND p.Playlist_id=?
         GROUP BY p.Playlist_id, p.Playlist_name",
        "ii", [$user_id, $view_pid]
    );
    if ($okv) {
        [$rows,$cv] = fetch_all_assoc($outv[0], $outv[1]); $cv();
        if ($rows) {
            $playlist_view = $rows[0];
            [$okt,$errt,$outt] = pexec($conn,
                "SELECT m.music_id, m.`Music name` AS title, m.artist_name, m.mood, m.file_path
                 FROM added_to a
                 JOIN Music m ON m.music_id = a.music_id
                 WHERE a.Member_id=? AND a.playlist_id=?
                 ORDER BY m.music_id DESC",
                "ii", [$user_id, $view_pid]
            );
            if ($okt) { [$playlist_tracks,$ct] = fetch_all_assoc($outt[0], $outt[1]); $ct(); }
        } else {
            $flash = "⚠️ That playlist isn’t in your library.";
            $view_pid = 0;
        }
    } else {
        $flash = "❌ Could not open playlist: ".h($errv);
    }
}

$moods = ["Happy","Sad","Romantic","Energetic","Chill","Workout","Sleep"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Music & Playlists</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
  :root{ --bg:#ffd1d1; --card:#fff4f4; --ink:#141414; --muted:#6b7280; --pill:#ffb0b0; --accent:#e68aa0;}
  body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Inter,Arial;background:var(--bg);color:var(--ink);}
  .wrap{max-width:980px;margin:20px auto;padding:0 16px}
  a{color:#d63838;text-decoration:none}
  h1{margin:0 0 10px}
  .card{background:var(--card);border-radius:14px;box-shadow:0 10px 26px rgba(0,0,0,.08);padding:16px;margin:12px 0}
  .pill{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:var(--pill);margin-right:6px;font-size:.9rem}
  .pill a{color:#111}
  .pill.active{background:#fbeaee;border:1px solid #fffdfe}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .btn{display:inline-block;background:var(--accent);color:#fdfdfd;border:none;border-radius:8px;padding:8px 12px;font-weight:600;cursor:pointer}
  .btn:hover{filter:brightness(1.05)}
  .btn-sm{padding:6px 10px;font-size:.9rem}
  .muted{color:var(--muted)}
  .flash{background:#f9fffc;border:1px solid #bbf7d0;padding:10px 12px;border-radius:10px;margin:10px 0}
  .list li{margin:6px 0}
  .back{display:inline-block;margin-bottom:8px}
  .grid3{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
  input,select{width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:8px}
  .toolbar{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
  audio{width:100%;margin-top:6px}
  .emoji{font-size:1.05rem}
</style>
</head>
<body>
  <div class="wrap">
    <a class="back" href="home.php">← Back to home</a>

    <h1>🎵 Music & Playlists</h1>

    <?php if ($flash): ?>
      <div class="flash"><?= $flash ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="toolbar">
        <strong>Filter by mood:</strong>
        <?php foreach ($moods as $m): ?>
          <?php $emo = $moodEmojis[$m] ?? '🎵'; ?>
          <span class="pill <?= $m === $mood ? 'active':'' ?>">
            <span class="emoji"><?= h($emo) ?></span>
            <a href="?mood=<?= urlencode($m) ?>"><?= h($m) ?></a>
          </span>
        <?php endforeach; ?>
        <span class="pill"><span class="emoji">✨</span><a href="music.php">Show All</a></span>
      </div>
    </div>

    <div class="row">
      <div class="card">
        <h3>Create quick mood playlist</h3>
        <form method="post" class="toolbar">
          <select name="mood_pick" required>
            <option value="">Select mood…</option>
            <?php foreach ($moods as $m): ?>
              <option value="<?= h($m) ?>" <?= $m===$mood?'selected':'' ?>>
                <?= h(($moodEmojis[$m] ?? '🎵')." ".$m) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <input type="text" name="mood_name" placeholder="Playlist name (optional)">
          <button class="btn" type="submit" name="create_mood">Create</button>
        </form>
        <p class="muted">This will add all songs that match the mood.</p>
      </div>
    </div>

    <div class="card">
      <h3>Your playlists</h3>
      <?php if (!$pls): ?>
        <p class="muted">No playlists yet. Create one above!</p>
      <?php else: ?>
        <div class="grid3">
        <?php foreach ($pls as $pl): ?>
          <div class="card" style="padding:12px">
            <strong><?= h($pl['Playlist_name']) ?></strong>
            <span class="muted">(<?= (int)$pl['tracks'] ?> tracks)</span>
            <div style="margin-top:8px">
              <a class="btn btn-sm" href="?view=<?= (int)$pl['Playlist_id'] ?>">Open</a>
              <form method="post" style="display:inline" onsubmit="return confirm('Remove this playlist from your library?');">
                <input type="hidden" name="playlist_id" value="<?= (int)$pl['Playlist_id'] ?>">
                <button class="btn btn-sm" style="background:#ef8a8a" type="submit" name="delete_pl">Delete</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($view_pid > 0 && $playlist_view): ?>
      <div class="card">
        <h3>Playlist: <?= h($playlist_view['Playlist_name']) ?></h3>
        <?php if (!$playlist_tracks): ?>
          <p class="muted">No songs in this playlist yet.</p>
        <?php else: ?>
          <ul class="list">
            <?php foreach ($playlist_tracks as $s): ?>
              <li>
                <strong><?= h($s['title']) ?></strong> — <?= h($s['artist_name']) ?>
                <?php if (!empty($s['file_path'])): ?>
                  <audio controls preload="none">
                    <source src="<?= h($s['file_path']) ?>" type="audio/mpeg">
                    Your browser can’t play this audio.
                  </audio>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($mood !== ""): ?>
      <div class="card">
        <h3>Suggested for mood: <span class="emoji"><?= h($moodEmojis[$mood] ?? '🎵') ?></span> <?= h($mood) ?></h3>
        <?php if (!$suggest): ?>
          <p class="muted">No suggestions yet for this mood.</p>
        <?php else: ?>
          <ul class="list">
            <?php foreach ($suggest as $s): ?>
              <li>
                <strong><?= h($s['title']) ?></strong> — <?= h($s['artist_name']) ?>
                <?php if (!empty($s['file_path'])): ?>
                  <audio controls preload="none">
                    <source src="<?= h($s['file_path']) ?>" type="audio/mpeg">
                    Your browser can’t play this audio.
                  </audio>
                <?php endif; ?>
                <?php if ($pls): ?>
                  <form method="post" style="display:inline">
                    <input type="hidden" name="music_id" value="<?= (int)$s['music_id'] ?>">
                    <select name="playlist_id" required>
                      <option value="">Add to…</option>
                      <?php foreach ($pls as $pl): ?>
                        <option value="<?= (int)$pl['Playlist_id'] ?>"><?= h($pl['Playlist_name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button class="btn btn-sm" type="submit" name="add_one">Add</button>
                  </form>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <div class="card">
        <h3>All songs for mood: <span class="emoji"><?= h($moodEmojis[$mood] ?? '🎵') ?></span> <?= h($mood) ?></h3>
        <?php if (!$all_mood): ?>
          <p class="muted">Nothing found.</p>
        <?php else: ?>
          <div class="grid3">
            <?php foreach ($all_mood as $s): ?>
              <div class="card" style="padding:12px">
                <div><strong><?= h($s['title']) ?></strong></div>
                <div class="muted"><?= h($s['artist_name']) ?> · <?= h($s['mood']) ?></div>
                <?php if (!empty($s['file_path'])): ?>
                  <audio controls preload="none">
                    <source src="<?= h($s['file_path']) ?>" type="audio/mpeg">
                    Your browser can’t play this audio.
                  </audio>
                <?php endif; ?>
                <div style="margin-top:6px">
                  <?php if ($pls): ?>
                    <form method="post" style="display:inline">
                      <input type="hidden" name="music_id" value="<?= (int)$s['music_id'] ?>">
                      <select name="playlist_id" required>
                        <option value="">Add to…</option>
                        <?php foreach ($pls as $pl): ?>
                          <option value="<?= (int)$pl['Playlist_id'] ?>"><?= h($pl['Playlist_name']) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button class="btn btn-sm" type="submit" name="add_one">Add</button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>

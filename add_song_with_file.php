<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/dbconnect.php'; 


$UPLOAD_DIR = __DIR__ . '/uploads/music';        
$PUBLIC_PREFIX = 'uploads/music';                
if (!is_dir($UPLOAD_DIR)) {
    @mkdir($UPLOAD_DIR, 0775, true);
}

$conn->query("ALTER TABLE `Music` ADD COLUMN `file_path` VARCHAR(500) NULL");

function guess_mood($name) {
    $n = strtolower($name);
    foreach ([
        'sleep' => 'Sleeping',
        'workout' => 'Energetic',
        'exercise' => 'Energetic',
        'gym' => 'Energetic',
        'romantic' => 'Romantic',
        'love' => 'Romantic',
        'sad' => 'Sad',
        'happy' => 'Happy',
        'chill' => 'Chill',
        'emotional' => 'Emotional'
    ] as $key => $mood) {
        if (strpos($n, $key) !== false) return $mood;
    }
    return 'Unknown';
}

// Try to parse "Artist - Title.mp3"
function parse_artist_title($filenameNoExt) {
    $parts = preg_split('/\s*-\s*/', $filenameNoExt, 2);
    if (count($parts) === 2) {
        return [$parts[0], $parts[1]];
    }
    // fallback: unknown artist, title is filename
    return ['Unknown Artist', $filenameNoExt];
}

// Insert (or skip if same file_path already exists)
function insert_song($conn, $title, $artist, $mood, $type, $format, $filePathRel) {
    // skip duplicates by path
    $chk = $conn->prepare("SELECT music_id FROM Music WHERE file_path = ?");
    $chk->bind_param("s", $filePathRel);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows > 0) { $chk->close(); return ['skipped' => true]; }
    $chk->close();

    // Some dumps include youtube_url, some don’t. Detect columns at runtime.
    $cols = [];
    $res = $conn->query("SHOW COLUMNS FROM Music");
    while ($row = $res->fetch_assoc()) { $cols[] = $row['Field']; }
    $hasYT = in_array('youtube_url', $cols);
    $hasFP = in_array('file_path', $cols);

    if ($hasYT && $hasFP) {
        $sql = "INSERT INTO Music (`Music name`, `mood`, `artist_name`, `type`, `Music_type`, `youtube_url`, `file_path`)
                VALUES (?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $emptyYT = null; // or '' if you prefer
        $stmt->bind_param("sssssss", $title, $mood, $artist, $type, $format, $emptyYT, $filePathRel);
    } else {
        // Minimal set guaranteed by your schema
        $sql = "INSERT INTO Music (`Music name`, `mood`, `artist_name`, `type`, `Music_type`, `file_path`)
                VALUES (?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $title, $mood, $artist, $type, $format, $filePathRel);
    }

    if ($stmt->execute()) {
        $stmt->close();
        return ['inserted' => true, 'id' => $conn->insert_id];
    }
    $err = $stmt->error;
    $stmt->close();
    return ['error' => $err];
}

$flash = "";

// --- If a ZIP is uploaded, extract it ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_zip']) && isset($_FILES['music_zip'])) {
    if (is_uploaded_file($_FILES['music_zip']['tmp_name'])) {
        $zipPath = $_FILES['music_zip']['tmp_name'];
        $zip = new ZipArchive();
        if ($zip->open($zipPath) === TRUE) {
            $zip->extractTo($UPLOAD_DIR);
            $zip->close();
            $flash = "ZIP extracted to uploads/music.";
        } else {
            $flash = "Failed to open ZIP file.";
        }
    } else {
        $flash = "No ZIP uploaded.";
    }
}

// --- Scan folder and import ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scan_import'])) {
    $files = glob($UPLOAD_DIR . '/*.mp3');
    $created = 0; $skipped = 0; $failed = 0;

    foreach ($files as $abs) {
        $base = basename($abs);
        $nameNoExt = preg_replace('/\.mp3$/i', '', $base);
        [$artist, $title] = parse_artist_title(str_replace('_', ' ', $nameNoExt));
        $mood   = guess_mood($nameNoExt);
        $type   = 'Pop';      // default genre (change if you like)
        $format = 'Classic';  // default sub-type

        $relPath = $PUBLIC_PREFIX . '/' . $base;
        $res = insert_song($conn, $title, $artist, $mood, $type, $format, $relPath);

        if (!empty($res['inserted'])) $created++;
        elseif (!empty($res['skipped'])) $skipped++;
        else { $failed++; }
    }

    $flash = "Imported: $created, skipped (duplicates): $skipped, failed: $failed.";
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Import MP3s → Music DB</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body{font-family:system-ui,-apple-system,Segoe UI,Inter,Arial;background:#fdecef;margin:0}
  .wrap{max-width:900px;margin:32px auto;padding:0 16px}
  .card{background:#fff;border-radius:14px;box-shadow:0 10px 28px rgba(0,0,0,.08);padding:18px}
  h1{margin:0 0 10px}
  .flash{background:#effaf1;border:1px solid #cfe8d5;padding:10px 12px;border-radius:10px;margin:12px 0}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  label{font-weight:600;display:block;margin-bottom:6px}
  .btn{background:#e98ca3;color:#fff;border:none;border-radius:8px;padding:10px 14px;font-weight:700;cursor:pointer}
  .btn:hover{filter:brightness(1.05)}
  .muted{color:#666}
  .mt{margin-top:10px}
</style>
</head>
<body>
<div class="wrap">
  <a href="admin_home.php">← Back to dashboard</a>
  <div class="card">
    <h1>🎵 Import MP3s into Music</h1>

    <?php if ($flash): ?><div class="flash"><?= htmlspecialchars($flash) ?></div><?php endif; ?>

    <div class="row">
      <form method="post" enctype="multipart/form-data">
        <label>Upload a ZIP of MP3s</label>
        <input type="file" name="music_zip" accept=".zip">
        <div class="mt"><button class="btn" name="upload_zip" type="submit">Upload & Extract</button></div>
        <p class="muted">Files will be extracted to <code><?= htmlspecialchars($PUBLIC_PREFIX) ?></code>.</p>
      </form>

      <form method="post">
        <label>Or import what's already in <code><?= htmlspecialchars($PUBLIC_PREFIX) ?></code></label>
        <button class="btn" name="scan_import" type="submit">Scan & Import</button>
        <p class="muted">The importer will parse filenames like <em>Artist - Title.mp3</em>. It guesses mood from keywords (sleep, workout, sad, happy, romantic, etc.). You can edit later in your music admin.</p>
      </form>
    </div>

    <hr class="mt">
    <p class="muted">Tip: Storing full MP3 binaries in MySQL is not recommended. Save files on disk and store the path in the DB — exactly what this tool does.</p>
  </div>
</div>
</body>
</html>

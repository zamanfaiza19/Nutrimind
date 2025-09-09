<?php
require __DIR__ . '/dbconnect.php';

$publicBase = '/uploads/music';
$folder     = __DIR__ . $publicBase;

if (!is_dir($folder)) {
    http_response_code(500);
    echo "Folder not found: $folder";
    exit;
}

// ensure file_path column exists 
$conn->query("CREATE TABLE IF NOT EXISTS Music LIKE Music"); 
$conn->query("ALTER TABLE `Music` ADD COLUMN IF NOT EXISTS `file_path` VARCHAR(255) NOT NULL AFTER `Music name`");

$insert = $conn->prepare("
  INSERT INTO `Music` (`Music name`, `mood`, `artist_name`, `type`, `Music_type`, `file_path`)
  SELECT ?, 'unknown', '', 'audio/mpeg', 'mp3', ?
  FROM DUAL
  WHERE NOT EXISTS (SELECT 1 FROM `Music` WHERE `file_path` = ?)
");

$added = 0; $skipped = 0;
$files = glob($folder . '/*.mp3');
foreach ($files as $full) {
    $file = basename($full);
    $title = preg_replace('/[_%-]+/', ' ', pathinfo($file, PATHINFO_FILENAME));
    $title = trim(preg_replace('/\s+/', ' ', $title));
    $path  = $publicBase . '/' . $file;

    $insert->bind_param('sss', $title, $path, $path);
    if ($insert->execute() && $insert->affected_rows > 0) $added++;
    else $skipped++;
}

echo "Done. Added: $added, Skipped (already existed): $skipped";
?>
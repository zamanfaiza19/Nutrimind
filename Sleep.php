<?php
session_start();
require_once __DIR__ . '/dbconnect.php'; 
if (!isset($_SESSION['user_id'])) {
  header("Location: /My_Project/auth.php");
  exit;
}
$memberId   = (int)$_SESSION['user_id'];
$memberName = htmlspecialchars($_SESSION['user_name'] ?? 'Friend', ENT_QUOTES, 'UTF-8');

$flashMsg = '';
$flashKind = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
  $hoursRaw = $_POST['time'] ?? '';

  if (!is_numeric($hoursRaw)) {
    $flashMsg = '❌ Please enter hours as a number.'; $flashKind = 'err';
  } else {
    $hours = (int)$hoursRaw;
    if ($hours < 0 || $hours > 24) {
      $flashMsg = '❌ Hours must be between 0 and 24.'; $flashKind = 'err';
    } else {
      if ($hours < 4) {
        $quality = "Poor";
      } elseif ($hours <= 6) {
        $quality = "Fair";
      } elseif ($hours <= 9) {
        $quality = "Excellent";
      } else {
        $quality = "Over slept";
      }

      // Insert 
      $ins = $conn->prepare("INSERT INTO `Sleep_track` (`Sleep_quality`, `time`) VALUES (?, ?)");
      $ins->bind_param("si", $quality, $hours);
      if ($ins->execute()) {
        $sleepId = $conn->insert_id;
        $ins->close();

        $link = $conn->prepare("INSERT INTO `checks` (`Sleep_id`, `member_id`) VALUES (?, ?)");
        $link->bind_param("ii", $sleepId, $memberId);
        if ($link->execute()) {
          if ($hours >= 7) {
            $flashMsg  = "🌙😴 Great job, {$memberName}! You slept <b>{$hours}h</b> — healthy sleep! 💪 (Quality: <b>{$quality}</b>)";
            $flashKind = 'ok';
          } else {
            $flashMsg  = "📝 Logged <b>{$hours}h</b> (Quality: <b>{$quality}</b>). Aim for 7+ hours for better recovery. ✨";
            $flashKind = 'warn';
          }
        } else {
          $flashMsg = '❌ Could not link the entry to your account.'; $flashKind = 'err';
        }
        $link->close();
      } else {
        $flashMsg = '❌ Could not save sleep entry.'; $flashKind = 'err';
        $ins->close();
      }
    }
  }
}

// track
$recent = [];
$sql = "SELECT st.`Sleep_id`, st.`Sleep_quality`, st.`time`
        FROM `checks` c
        JOIN `Sleep_track` st ON st.`Sleep_id` = c.`Sleep_id`
        WHERE c.`member_id` = ?
        ORDER BY st.`Sleep_id` DESC
        LIMIT 20";
$st = $conn->prepare($sql);
$st->bind_param("i", $memberId);
$st->execute();
$res = $st->get_result();
while ($row = $res->fetch_assoc()) $recent[] = $row;
$st->close();

// weekly average
$avg7 = null;
$sqlAvg = "SELECT AVG(t.`time`) AS avg_h FROM (
             SELECT st.`time`
             FROM `checks` c
             JOIN `Sleep_track` st ON st.`Sleep_id` = c.`Sleep_id`
             WHERE c.`member_id` = ?
             ORDER BY st.`Sleep_id` DESC
             LIMIT 7
           ) t";
$sa = $conn->prepare($sqlAvg);
$sa->bind_param("i", $memberId);
$sa->execute();
$avg7 = ($sa->get_result()->fetch_assoc()['avg_h']) ?? null;
$sa->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Nutrimind — Sleep Tracker</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#fff2f6; --panel:#ffffff; --ink:#111827; --muted:#6b7280;
    --border:#f5d4e0; --brand:#ef6d9f;
    --ok:#16a34a; --okbg:#dcfce7;
    --warn:#b45309; --warnbg:#fef3c7;
    --err:#b91c1c; --errbg:#fee2e2;
  }
  *{box-sizing:border-box}
  body{margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;background:var(--bg);color:var(--ink);
       min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px;}
  .wrap{width:min(940px,96vw);display:grid;gap:16px}
  .card{background:var(--panel);border:1px solid var(--border);border-radius:18px;box-shadow:0 10px 28px rgba(0,0,0,.06);padding:18px}
  header{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
  h1{margin:0;font-size:1.4rem}
  .muted{color:var(--muted)}
  form{display:grid;grid-template-columns:2fr 1fr;gap:10px;margin-top:10px}
  @media (max-width:800px){ form{grid-template-columns:1fr} }
  select,input,button{width:100%;padding:12px;border:1px solid var(--border);border-radius:12px;background:#fff}
  button{background:linear-gradient(135deg,#ef6d9f,#f58fb5);color:#fff;font-weight:800;cursor:pointer;border:none}
  button:hover{filter:brightness(1.05)}
  .flash{padding:10px;border-radius:12px;border:1px solid;margin-top:12px}
  .flash.ok{background:var(--okbg);border-color:rgba(22,163,74,.4);color:var(--ok)}
  .flash.warn{background:var(--warnbg);border-color:rgba(180,83,9,.4);color:var(--warn)}
  .flash.err{background:var(--errbg);border-color:rgba(185,28,28,.4);color:var(--err)}
  .grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
  @media (max-width:900px){ .grid{grid-template-columns:1fr} }
  table{width:100%;border-collapse:collapse;border:1px solid var(--border);border-radius:12px;overflow:hidden}
  th,td{padding:10px 12px;border-bottom:1px solid var(--border);text-align:left}
  th{background:#fff6fa}
  .pill{display:inline-block;padding:4px 10px;border-radius:999px;background:#f3f4f6;font-weight:700}
  a.back{display:inline-block;margin-top:6px;text-decoration:none;color:#7c3aed}
</style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <header>
        <h1>😴 Sleep Tracker — Hi, <?= $memberName; ?>!</h1>
        <a class="back" href="home.php">← Back to Home</a>
      </header>
      <p class="muted">Enter your sleep hours; we’ll automatically classify the quality. Hit <b>7+ hours</b> for a healthy sleep shout-out 🌙💪</p>

      <?php if ($flashMsg): ?>
        <div class="flash <?= $flashKind ?>"><?= $flashMsg ?></div>
      <?php endif; ?>

      <form method="post">
        <!-- Removed the quality dropdown; only hours input now -->
        <input type="number" name="time" min="0" max="24" step="1" placeholder="Hours slept (e.g., 7)" required>
        <button type="submit" name="save" value="1">Save</button>
      </form>
    </div>

    <div class="grid">
      <div class="card">
        <h2 style="margin:0 0 8px 0">📈 Your last 7 entries — Average</h2>
        <p class="muted" style="margin:0">
          <?= $avg7 !== null ? "Average: <b>".number_format((float)$avg7, 2)." h</b>" : "No entries yet." ?>
        </p>
      </div>

      <div class="card">
        <h2 style="margin:0 0 8px 0">📜 Recent entries</h2>
        <?php if ($recent): ?>
          <table>
            <thead><tr><th>#</th><th>Quality</th><th>Hours</th></tr></thead>
            <tbody>
            <?php foreach ($recent as $r): ?>
              <tr>
                <td><?= (int)$r['Sleep_id'] ?></td>
                <td><span class="pill"><?= htmlspecialchars($r['Sleep_quality']) ?></span></td>
                <td><?= (int)$r['time'] ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="muted">No sleep logs yet — add your first one above.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>

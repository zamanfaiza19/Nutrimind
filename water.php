<?php
session_start();
require_once __DIR__ . '/dbconnect.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: /My_Project/home.php");
  exit;
}

$memberId  = (int)$_SESSION['user_id'];
$firstName = htmlspecialchars($_SESSION['user_name'] ?? 'Friend');
$today     = date('Y-m-d');

/* Hero hydration photo */
$heroUrl = 'https://plus.unsplash.com/premium_photo-1674605369311-4a869688bfe1?fm=jpg&q=60&w=1600';


$heightCm = 0; $weightKg = 0;
$stmt = $conn->prepare("SELECT Height_cm, Weight_kg FROM member WHERE Member_id=?");
$stmt->bind_param("i", $memberId);
$stmt->execute();
$stmt->bind_result($heightCm, $weightKg);
$stmt->fetch();
$stmt->close();
if ($heightCm <= 0) $heightCm = 170;
if ($weightKg <= 0) $weightKg = 70;

function compute_target_ml($kg, $cm){
  $ml = (int)round($kg * 35);
  if ($cm > 180) $ml += 250;
  if ($cm < 160) $ml -= 250;
  return max(1500, min(5000, $ml));
}
$defaultTarget = compute_target_ml($weightKg, $heightCm);

$intakeId = null; $reminder = 'None'; $dbTarget = 0; $waterMl = 0;

$sel = $conn->prepare(
  "SELECT intake_id, reminder, target_intake, water_intake
     FROM water
    WHERE member_id=? AND log_date=?"
);
$sel->bind_param("is", $memberId, $today);
$sel->execute();
$sel->store_result();

if ($sel->num_rows > 0) {
  $sel->bind_result($intakeId, $reminder, $dbTarget, $waterMl);
  $sel->fetch();
  if ((int)$dbTarget < 500) {
    $dbTarget = $defaultTarget;
    $fix = $conn->prepare("UPDATE water SET target_intake=? WHERE intake_id=?");
    $fix->bind_param("ii", $dbTarget, $intakeId);
    $fix->execute(); $fix->close();
  }
} else {
  $sel->close();
  $ins = $conn->prepare(
    "INSERT INTO water (member_id, log_date, reminder, target_intake, water_intake)
     VALUES (?, ?, 'None', ?, 0)"
  );
  $ins->bind_param("isi", $memberId, $today, $defaultTarget);
  $ins->execute(); $intakeId = $ins->insert_id; $ins->close();

  $sel = $conn->prepare(
    "SELECT intake_id, reminder, target_intake, water_intake
       FROM water
      WHERE member_id=? AND log_date=?"
  );
  $sel->bind_param("is", $memberId, $today);
  $sel->execute();
  $sel->bind_result($intakeId, $reminder, $dbTarget, $waterMl);
  $sel->fetch();
}
$sel->close();

$flash = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (isset($_POST['add_custom_ml'])) {
    $add = (int)$_POST['add_custom_ml'];
    if ($add > 0 && $add <= 5000) {
      $upd = $conn->prepare("UPDATE water SET water_intake = water_intake + ? WHERE intake_id=?");
      $upd->bind_param("ii", $add, $intakeId);
      $upd->execute(); $upd->close();
      $waterMl += $add;
      $flash = "Added {$add} ml.";
    } else {
      $flash = "Please enter a valid amount (1–5000 ml).";
    }
  }

  if (isset($_POST['save_settings'])) {
    $newReminder = trim($_POST['reminder'] ?? 'None');
    $upd = $conn->prepare("UPDATE water SET reminder=? WHERE intake_id=?");
    $upd->bind_param("si", $newReminder, $intakeId);
    $upd->execute(); $upd->close();
    $reminder = htmlspecialchars($newReminder, ENT_QUOTES, 'UTF-8');
    $flash = "Reminder updated.";
  }
}

$remaining = max(0, $dbTarget - $waterMl);
$progress  = $dbTarget > 0 ? min(100, round(($waterMl / $dbTarget) * 100)) : 0;

function coach_line($remaining, $progress){
  if ($remaining <= 0) return "Legend! You’ve smashed your goal today 💧";
  if ($remaining <= 100) return "Amazing — only {$remaining} ml to go! 💪";
  if ($progress >= 75)  return "So close — one more glass and you’re there!";
  if ($progress >= 50)  return "Halfway there — keep sipping!";
  if ($progress > 0)    return "Nice start! Small sips add up.";
  return "Let’s begin with your first glass.";
}
$coach = coach_line($remaining, $progress);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Nutrimind — Hydration</title>
<style>
  body{
    margin:0; min-height:100vh; display:flex; justify-content:center; align-items:flex-start;
    padding:40px 16px;
    font-family: system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;
    background: linear-gradient(135deg, #fde2e4 0%, #fad0c4 100%);
  }
  .card{
    background:#fff; width:500px; padding:22px; border-radius:16px;
    box-shadow:0 12px 34px rgba(0,0,0,.12);
    display:flex; flex-direction:column; gap:12px; text-align:center;
  }
  .hero{
    width:100%; height:160px; border-radius:12px;
    object-fit:cover;
    object-position:center 35%;
    display:block;
    box-shadow:0 6px 18px rgba(0,0,0,.08);
  }
  h1{ margin:6px 0 2px; font-size:1.6rem; }
  .sub{ color:#6b7280; margin:0 0 8px; }
  .panel{ background:#f8fafc; border:1px solid #eef; border-radius:12px; padding:12px; }
  .label{ color:#6b7280; font-size:.92rem; }
  .num{ font-size:1.28rem; font-weight:700; }
  .progress{ width:100%; height:10px; background:#eef2f7; border-radius:999px; overflow:hidden; }
  .bar{ height:100%; width:<?= $progress ?>%; background:#7c3aed; }
  .muted{ color:#6b7280; font-size:.95rem; }
  .field{ width:100%; padding:12px; border-radius:10px; border:1px solid #e5e7eb; font-size:1rem; }
  .btn{
    display:inline-block; padding:12px 16px; border:none; border-radius:12px; cursor:pointer; font-weight:700;
    color:#fff; background:linear-gradient(135deg,#8b5cf6,#7c3aed); transition:transform .1s ease, background .2s ease;
  }
  .btn:hover{ transform:scale(1.02); background:linear-gradient(135deg,#9f7aea,#6d28d9); }
  .link{ color:#7c3aed; text-decoration:none; font-weight:600; }
  .msg{ color:#7c3aed; font-weight:600; }
  .coach{ background:#f5f3ff; color:#4c1d95; border:1px solid #e9d5ff; padding:10px; border-radius:12px; font-weight:600; }
  .coach-small{ color:#4c1d95; font-weight:600; margin:6px 0 0; }
</style>
</head>
<body>
  <div class="card">
    <img class="hero" src="<?= htmlspecialchars($heroUrl, ENT_QUOTES) ?>" alt="Hydration water" loading="eager">

    <h1>Hydration</h1>
    <p class="sub">Hi <?= $firstName ?>, track your water for <b><?= htmlspecialchars($today) ?></b>.</p>

    <?php if ($flash): ?><div class="msg"><?= htmlspecialchars($flash) ?></div><?php endif; ?>

    <div class="panel">
      <div class="label">Target (ml)</div>
      <div class="num"><?= (int)$dbTarget ?></div>
    </div>
    <div class="panel">
      <div class="label">Consumed (ml)</div>
      <div class="num"><?= (int)$waterMl ?></div>
      <div class="label">Left: <b><?= (int)$remaining ?> ml</b></div>
    </div>

    <div class="progress"><div class="bar"></div></div>
    <div class="muted"><?= $progress ?>% of goal</div>

    <form method="post" style="margin-top:6px;">
      <input class="field" type="number" step="1" min="1" max="5000" name="add_custom_ml" placeholder="Type how much you drank (e.g., 175)">
      <div style="margin-top:8px;">
        <button class="btn" type="submit">Add Water</button>
      </div>
    </form>

    <div class="coach"><?= htmlspecialchars($coach) ?></div>

    <form method="post" style="text-align:left; margin-top:6px;">
      <label class="label">Reminder</label>
      <select class="field" name="reminder">
        <?php
          $opts = ['None','Every 2 hours','Morning & Evening','Hourly (light)','Custom note'];
          foreach ($opts as $o) {
            $sel = ($reminder === $o) ? 'selected' : '';
            echo "<option $sel>".htmlspecialchars($o)."</option>";
          }
        ?>
      </select>
      <div class="coach-small">
        <?= $remaining > 0
            ? "Great sip — only <b>" . (int)$remaining . " ml</b> to go. You’ve got this! 💧"
            : "Goal achieved — amazing consistency today! 🎉"; ?>
      </div>

      <div style="margin-top:10px;">
        <button class="btn" name="save_settings" value="1">Save Settings</button>
      </div>
    </form>

    <div style="text-align:center; margin-top:8px;">
      <a class="link" href="intake.php">← Back to Daily Intake</a>
    </div>
  </div>
</body>
</html>
<?php
/* =======================
   Exercise Log — Nutrimind
   Requires table `exercise` with columns:
     exercise_id INT, time_data DATE, duration INT,
     types_of_exercise VARCHAR(255), calories_burned INT, reminder VARCHAR(255)
   PRIMARY KEY (exercise_id, time_data)
   ======================= */

session_start();
require_once "dbconnect.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: /My_Project/auth.php");
  exit;
}

$memberId  = (int)$_SESSION["user_id"];
$userName  = htmlspecialchars($_SESSION["user_name"] ?? "Friend");
$firstName = htmlspecialchars(explode(' ', trim($userName))[0]);
$msg = "";

/* ---------- profile → daily target ---------- */
$weightKg = 0; $n=$o=$ob="";
$stmt = $conn->prepare("SELECT Weight_kg, Normal, Overweight, Obese FROM member WHERE Member_id=?");
$stmt->bind_param("i", $memberId);
$stmt->execute();
$stmt->bind_result($weightKg,$n,$o,$ob);
$stmt->fetch(); $stmt->close();
if ($weightKg <= 0) $weightKg = 70;

function daily_target($n,$o,$ob){ if($ob==="Yes") return 700; if($o==="Yes") return 500; return 300; }
$targetKcal = daily_target($n,$o,$ob);

/* ---------- MET dictionary (internal only) ---------- */
$MET = [
  "Walking (4 km/h)"=>3.0, "Walking (5.5 km/h brisk)"=>4.3,
  "Running (8 km/h)"=>8.3, "Running (10 km/h)"=>10.0,
  "Treadmill (walk 5 km/h)"=>4.3, "Treadmill (run 8–10 km/h)"=>9.0,
  "Elliptical trainer"=>5.0, "Stair climber / Step machine"=>8.8,
  "Rowing machine (moderate)"=>7.0, "Rowing machine (vigorous)"=>8.5,
  "Cycling (light <16 km/h)"=>4.0, "Cycling (16–19 km/h)"=>6.8,
  "Stationary bike (moderate)"=>7.0, "Stationary bike (vigorous)"=>8.8,
  "Jump rope (slow-moderate)"=>8.8, "Jump rope (fast)"=>12.3,
  "Swimming (leisure)"=>6.0, "Swimming laps (moderate)"=>8.3, "Swimming laps (vigorous)"=>10.0,
  "Yoga (Hatha)"=>3.0, "Yoga (Power)"=>4.0,
  "Leg press (machine)"=>5.0, "Leg extension (machine)"=>3.5,
  "Deadlift (moderate)"=>6.0, "Bench press (moderate)"=>5.0,
  "Push-ups (continuous)"=>8.0, "Pull-ups / Chin-ups"=>8.0,
  "Gym (mixed)"=>5.0, "Walking"=>3.5, "Running"=>8.0, "Cycling"=>6.0, "Yoga"=>3.0, "Swimming"=>8.0
];

/* ---------- smart reminder text ---------- */
function tip_for_remaining($remaining) {
  if ($remaining <= 0) return "🔥 Amazing! You’ve hit today’s burn target. Recovery & hydration time. 💧";
  if ($remaining <= 100) return "✅ So close — only ~{$remaining} kcal left. 10 min brisk walk will finish it!";
  if ($remaining <= 250) return "🌟 Great work! About {$remaining} kcal to go. Try 20–25 min cycling or a short run.";
  return "💪 Good groove! ~{$remaining} kcal remaining. Plan one more session — you’ve got this!";
}

/* ---------- NEW ⬇  Reset Today (do this before any reads) ---------- */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["reset_today"])) {
  $del = $conn->prepare("DELETE FROM exercise WHERE exercise_id=? AND time_data=CURDATE()");
  $del->bind_param("i", $memberId);
  $del->execute();
  $del->close();
  $msg = "🗑️ Today's exercise log has been reset.";
}
/* ---------- NEW ⬆ ---------- */

/* ---------- create / update (UPSERT by (member, date)) ---------- */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create"])) {
  $type    = trim($_POST["types_of_exercise"] ?? "");
  $minutes = (int)($_POST["duration"] ?? 0);
  $dateStr = trim($_POST["date"] ?? date('Y-m-d'));                // YYYY-MM-DD
  $userRem = trim($_POST["reminder"] ?? "");                       // user-chosen mood or empty

  if ($type==="" || $minutes<=0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
      $msg = "❌ Select exercise, enter minutes > 0, and choose a valid date.";
  } else {
      $met  = $MET[$type] ?? 4.0;
      $kcal = (int) round(0.0175 * $met * $weightKg * $minutes);
      $remToSave = ($userRem!=="") ? $userRem : "…";

      // One row per (exercise_id, time_data)
      $sql = "INSERT INTO exercise
              (exercise_id, time_data, duration, types_of_exercise, calories_burned, reminder)
              VALUES (?,?,?,?,?,?)
              ON DUPLICATE KEY UPDATE
                duration = duration + VALUES(duration),
                calories_burned = calories_burned + VALUES(calories_burned),
                types_of_exercise = VALUES(types_of_exercise),
                reminder = VALUES(reminder)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("isisis", $memberId, $dateStr, $minutes, $type, $kcal, $remToSave); // i s i s i s
      $stmt->execute();
      $stmt->close();

      // Dashboard pills should reflect *today*:
      $todayTotal = 0;
      $q = $conn->prepare("SELECT COALESCE(SUM(calories_burned),0)
                           FROM exercise WHERE exercise_id=? AND time_data=CURDATE()");
      $q->bind_param("i", $memberId);
      $q->execute(); $q->bind_result($todayTotal); $q->fetch(); $q->close();

      if ($userRem==="") {
        $remaining = max(0, $targetKcal - (int)$todayTotal);
        $smart = tip_for_remaining($remaining);
        $u = $conn->prepare("UPDATE exercise SET reminder=? WHERE exercise_id=? AND time_data=?");
        $u->bind_param("sis", $smart, $memberId, $dateStr);
        $u->execute(); $u->close();
      }

      $msg = "✅ Saved: $minutes min of $type (~{$kcal} kcal) on $dateStr.";
  }
}

/* ---------- read: show latest row; pills show today's burn ---------- */
$row = null;

// latest saved entry (by date)
$q = $conn->prepare("SELECT time_data, duration, types_of_exercise, calories_burned, reminder
                     FROM exercise
                     WHERE exercise_id=?
                     ORDER BY time_data DESC
                     LIMIT 1");
$q->bind_param("i", $memberId);
$q->execute();
$row = $q->get_result()->fetch_assoc();
$q->close();

// today's total for the top badges
$todayTotal = 0;
$tq = $conn->prepare("SELECT COALESCE(SUM(calories_burned),0)
                      FROM exercise WHERE exercise_id=? AND time_data=CURDATE()");
$tq->bind_param("i", $memberId);
$tq->execute(); $tq->bind_result($todayTotal); $tq->fetch(); $tq->close();

$remaining = max(0, $targetKcal - (int)$todayTotal);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Exercise Log — Nutrimind</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
  :root{ --bg1:#fde2e2; --bg2:#fbc2eb; --card:#fff; --ink:#221b22; --pill:#f5f2f5; --btn:#e9c3c3; --btnH:#e8b3b3; --ok:#23a559; }
  body{ margin:0; font-family:"Segoe UI",system-ui,Arial,sans-serif; background:linear-gradient(135deg,var(--bg1),var(--bg2)); min-height:100vh; display:flex; align-items:flex-start; justify-content:center; color:var(--ink); padding:24px; }
  .wrap{ width:min(900px,95vw); }
  .card{ background:var(--card); border-radius:20px; padding:22px; box-shadow:0 18px 45px rgba(0,0,0,.18); margin:12px 0; }
  h2,h3{ margin:8px 0 12px }
  .meta{ display:flex; flex-wrap:wrap; gap:8px; margin:6px 0 2px }
  .pill{ background:var(--pill); border-radius:999px; padding:8px 12px; font-weight:600 }
  .msg{ margin:8px 0 0; font-weight:700; color:var(--ok) }
  label{ display:block; font-size:.92rem; margin:6px 0 4px }
  input,select{ width:100%; padding:10px 12px; border:1px solid #eee; border-radius:12px; background:#fff; outline:none; }
  .grid{ display:grid; grid-template-columns:1fr 1fr; gap:12px }
  .full{ grid-column:1/-1 }
  .btn{ background:var(--btn); border:none; color:#2d1f1f; font-weight:700; padding:12px 16px; border-radius:12px; cursor:pointer; }
  .btn:hover{ background:var(--btnH) }
  table{ width:100%; border-collapse:collapse; margin-top:8px }
  th,td{ border:1px solid #f0e7ef; padding:10px; text-align:center }
  th{ background:#faf6fa; }
  .footer{ text-align:center; margin-top:10px }
  .link{ color:#5a2a2a; text-decoration:none; font-weight:600 }

  .hero{
    width:100%; height:220px; border-radius:20px; overflow:hidden;
    position:relative; box-shadow:0 12px 30px rgba(0,0,0,.15);
    background:#ddd url('https://images.unsplash.com/photo-1554284126-aa88f22d8b74?q=80&w=1600&auto=format&fit=crop') center/cover no-repeat;
    margin-bottom:16px;
  }
  .hero::after{ content:""; position:absolute; inset:0; background:linear-gradient(120deg, rgba(253,226,226,.45), rgba(251,194,235,.35)); }
  .hero-badge{ position:absolute; left:18px; bottom:16px; background:rgba(255,255,255,.88); padding:10px 14px; border-radius:14px; font-weight:700; box-shadow:0 6px 18px rgba(0,0,0,.12); }
</style>
</head>
<body>
<div class="wrap">

  <!-- Hero -->
  <div class="hero">
    <div class="hero-badge">Keep calm & strong, <?= $userName ?> 🧘‍♀️</div>
  </div>

  <div class="card">
    <h2>🏋️ Exercise Log</h2>
    <div class="meta">
      <span class="pill">Hi, <?= htmlspecialchars($userName) ?>!</span>
      <span class="pill">Weight: <?= (int)$weightKg ?> kg</span>
      <span class="pill">Daily Target: <?= (int)$targetKcal ?> kcal</span>
      <span class="pill">Today: <?= (int)$todayTotal ?> kcal</span>
      <span class="pill">Remaining: <?= (int)$remaining ?> kcal</span>
    </div>

    <!-- NEW ⬇  Reset Today button (uses existing .btn style) -->
    <form method="POST" style="margin-top:10px;">
      <button class="btn" type="submit" name="reset_today" value="1"
              onclick="return confirm('Reset today\\'s exercise entries? This cannot be undone.')">
        🔄 Reset Today
      </button>
    </form>
    <!-- NEW ⬆ -->

    <?php if ($msg): ?><p class="msg"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
  </div>

  <div class="card">
    <h3>Add / Update Entry</h3>
    <form method="POST">
      <input type="hidden" name="create" value="1">
      <div class="grid">
        <div>
          <label>Date</label>
          <input type="date" name="date"
                 value="<?= htmlspecialchars($_POST['date'] ?? ($row['time_data'] ?? date('Y-m-d'))) ?>"
                 required>
        </div>
        <div>
          <label>Minutes</label>
          <input type="number" name="duration" min="1" placeholder="30" required>
        </div>
        <div class="full">
          <label>Exercise</label>
          <select name="types_of_exercise" required>
            <?php foreach($MET as $label=>$v): ?>
              <option value="<?= htmlspecialchars($label) ?>"><?= htmlspecialchars($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="full">
          <label>How are you feeling today, <?= $firstName ?>?</label>
          <select name="reminder">
            <option value="">Auto tip ✨ (let me suggest)</option>
            <option value="Feeling great 😄">Feeling great 😄</option>
            <option value="Energetic 💥">Energetic 💥</option>
            <option value="Motivated 🔥">Motivated 🔥</option>
            <option value="Okay 🙂">Okay 🙂</option>
            <option value="A bit tired 😴">A bit tired 😴</option>
            <option value="Sore 🫠">Sore 🫠</option>
            <option value="Stressed 😵‍💫">Stressed 😵‍💫</option>
            <option value="Under the weather 🤒">Under the weather 🤒</option>
          </select>
        </div>
        <div class="full">
          <button class="btn" type="submit">Save</button>
        </div>
      </div>
    </form>
  </div>

  <div class="card">
    <h3>Your Stored Entry</h3>
    <?php if ($row): ?>
      <table>
        <tr><th>Date</th><th>Minutes</th><th>Calories</th><th>Reminder</th></tr>
        <tr>
          <td><?= htmlspecialchars($row['time_data']) ?></td>
          <td><?= (int)($row['duration'] ?? 0) ?></td>
          <td><?= (int)($row['calories_burned'] ?? 0) ?></td>
          <td><?= htmlspecialchars($row['reminder'] ?? "") ?></td>
        </tr>
      </table>
    <?php else: ?>
      <p>No entry yet — add your first workout above.</p>
    <?php endif; ?>
    <div class="footer"><a class="link" href="home.php">⬅ Back to Home</a></div>
  </div>

</div>
</body>
</html>

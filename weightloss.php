<?php
session_start();
if (!isset($_SESSION["user_id"])) {
  header("Location: /My_Project/auth.php");
  exit;
}
$userName  = htmlspecialchars($_SESSION["user_name"] ?? "Friend");
$firstName = htmlspecialchars(explode(' ', trim($userName))[0]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Weight Loss — Nutrimind</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
  :root{ --bg1:#fde2e2; --bg2:#fbc2eb; --card:#fff; --ink:#221b22; --btn:#e9c3c3; --btnH:#e8b3b3; --pill:#f5f2f5; }
  *{ box-sizing:border-box }
  body{ margin:0; font-family:"Segoe UI",system-ui,Arial,sans-serif; color:var(--ink);
        background:linear-gradient(135deg,var(--bg1),var(--bg2)); min-height:100vh;
        display:flex; align-items:flex-start; justify-content:center; padding:24px; }
  .wrap{ width:min(900px,95vw); }
  .card{ background:var(--card); border-radius:20px; padding:24px; box-shadow:0 18px 45px rgba(0,0,0,.18); margin:12px 0; }
  h2,h3{ margin:8px 0 12px }
  ul{ margin:0; padding-left:18px; line-height:1.8 }
  .grid{ display:grid; grid-template-columns:1fr 1fr; gap:12px }
  .pill{ background:var(--pill); border-radius:999px; padding:8px 12px; font-weight:600; display:inline-block; margin:4px 6px 8px 0 }
  .btn{ display:inline-block; background:var(--btn); border:none; color:#2d1f1f; font-weight:700; padding:12px 16px; border-radius:12px; text-decoration:none; cursor:pointer }
  .btn:hover{ background:var(--btnH) }
  table{ width:100%; border-collapse:collapse; margin-top:8px }
  th,td{ border:1px solid #f0e7ef; padding:10px; text-align:center }
  th{ background:#faf6fa }
  .hero{
    width:100%; height:200px; border-radius:20px; overflow:hidden; position:relative;
    box-shadow:0 12px 30px rgba(0,0,0,.15);
    background:#ddd url('https://images.unsplash.com/photo-1534367610401-9f5ed68180aa?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat;
    margin-bottom:16px;
  }
  .hero::after{ content:""; position:absolute; inset:0; background:linear-gradient(120deg, rgba(253,226,226,.45), rgba(251,194,235,.35)); }
  .hero-badge{ position:absolute; left:18px; bottom:16px; background:rgba(255,255,255,.9); padding:10px 14px; border-radius:14px; font-weight:700; box-shadow:0 6px 18px rgba(0,0,0,.12); }
</style>
</head>
<body>
<div class="wrap">
  <div class="hero"><div class="hero-badge">Stay strong on your cut, <?= $firstName ?> 🔥</div></div>

  <div class="card">
    <h2>🔥 Weight Loss Plan</h2>
    <span class="pill">5–6 sessions/week</span>
    <span class="pill">Calorie deficit</span>
    <span class="pill">Cardio + Strength mix</span>
    <p>Combine cardio and resistance training with a mild calorie deficit. Consistency matters more than intensity!</p>
  </div>

  <div class="card">
    <h3>Effective Activities</h3>
    <div class="grid">
      <div>
        <h4>Cardio</h4>
        <ul>
          <li>Brisk Walking — 30–40 min</li>
          <li>Cycling (moderate pace) — 40 min</li>
          <li>Interval Running — 20–25 min</li>
          <li>Swimming (laps) — 30 min</li>
          <li>Jump Rope — 10–15 min</li>
        </ul>
      </div>
      <div>
        <h4>Strength / Core</h4>
        <ul>
          <li>Bodyweight Squats — 3×15</li>
          <li>Push-ups — 3×12</li>
          <li>Planks — 3×40s</li>
          <li>Lunges — 3×12/leg</li>
          <li>Yoga / Pilates — 20 min</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="card">
    <h3>Sample Weekly Schedule</h3>
    <table>
      <tr><th>Day</th><th>Workout</th><th>Notes</th></tr>
      <tr><td>Day 1</td><td>Brisk Walk + Core</td><td>Low impact starter</td></tr>
      <tr><td>Day 2</td><td>Cycling</td><td>Steady pace, 40 min</td></tr>
      <tr><td>Day 3</td><td>Yoga + Core</td><td>Flexibility focus</td></tr>
      <tr><td>Day 4</td><td>Interval Running</td><td>Jog & Sprint</td></tr>
      <tr><td>Day 5</td><td>Swimming</td><td>Full-body cardio</td></tr>
      <tr><td>Day 6</td><td>Strength (Bodyweight)</td><td>Squats, Push-ups, Planks</td></tr>
      <tr><td>Day 7</td><td>Rest / Light Stretch</td><td>Recovery</td></tr>
    </table>
  </div>

  <div class="card">
    <h3>Weight Loss Tips</h3>
    <ul>
      <li>Calorie deficit: ~400–500 kcal/day</li>
      <li>High-protein meals (keep you full)</li>
      <li>Drink 2–3 L water daily</li>
      <li>Prioritize sleep & stress control</li>
      <li>Don’t skip resistance training</li>
    </ul>
    <a class="btn" href="exercise.php">📝 Log a workout</a>
    <a class="btn" href="target.php" style="margin-left:8px">⬅ Back</a>
  </div>
</div>
</body>
</html>


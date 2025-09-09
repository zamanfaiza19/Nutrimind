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
<title>Muscle Gain — Nutrimind</title>
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
    background:#ddd url('https://images.unsplash.com/photo-1579758629938-03607ccdbaba?q=80&w=1600&auto=format&fit=crop') center/cover no-repeat;
    margin-bottom:16px;
  }
  .hero::after{ content:""; position:absolute; inset:0; background:linear-gradient(120deg, rgba(253,226,226,.45), rgba(251,194,235,.35)); }
  .hero-badge{ position:absolute; left:18px; bottom:16px; background:rgba(255,255,255,.9); padding:10px 14px; border-radius:14px; font-weight:700; box-shadow:0 6px 18px rgba(0,0,0,.12); }
</style>
</head>
<body>
<div class="wrap">
  <div class="hero"><div class="hero-badge">Grow lean muscle, <?= $firstName ?> 💪</div></div>

  <div class="card">
    <h2>🏆 Muscle Gain Plan</h2>
    <span class="pill">3–5 sessions/week</span>
    <span class="pill">Progressive overload</span>
    <span class="pill">Balanced split</span>
    <p>Focus on compound lifts, higher protein intake, and gradually increasing weights. Keep rest periods short for hypertrophy.</p>
  </div>

  <div class="card">
    <h3>Key Workouts</h3>
    <div class="grid">
      <div>
        <h4>Upper Body</h4>
        <ul>
          <li>Bench Press — 4×6–8</li>
          <li>Pull-ups / Lat Pulldown — 4×8–10</li>
          <li>Overhead Press — 3×6–8</li>
          <li>Barbell Rows — 4×6–8</li>
          <li>Dumbbell Curls — 3×10–12</li>
        </ul>
      </div>
      <div>
        <h4>Lower Body</h4>
        <ul>
          <li>Back Squat — 4×6–8</li>
          <li>Romanian Deadlift — 4×6–8</li>
          <li>Leg Press — 3×10–12</li>
          <li>Lunges — 3×12/leg</li>
          <li>Calf Raises — 4×12–15</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="card">
    <h3>Sample Weekly Split</h3>
    <table>
      <tr><th>Day</th><th>Focus</th><th>Key Work</th></tr>
      <tr><td>Day 1</td><td>Push</td><td>Bench, OHP, Incline DB Press</td></tr>
      <tr><td>Day 2</td><td>Pull</td><td>Pull-ups, Barbell Rows, Curls</td></tr>
      <tr><td>Day 3</td><td>Legs</td><td>Squat, RDL, Lunges, Calf Raises</td></tr>
      <tr><td>Day 4</td><td>Rest</td><td>Yoga / Mobility</td></tr>
      <tr><td>Day 5</td><td>Full Upper</td><td>Bench, Pull-ups, Overhead Press</td></tr>
    </table>
  </div>

  <div class="card">
    <h3>Muscle Gain Tips</h3>
    <ul>
      <li>Eat in a small calorie surplus (~250–300 kcal)</li>
      <li>Protein: 1.6–2.2 g/kg daily</li>
      <li>Track lifts & add weight/reps weekly</li>
      <li>Focus on sleep (7–9 hrs)</li>
      <li>Hydration supports recovery</li>
    </ul>
    <a class="btn" href="exercise.php">📝 Log a workout</a>
    <a class="btn" href="target.php" style="margin-left:8px">⬅ Back</a>
  </div>
</div>
</body>
</html>
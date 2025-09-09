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
<title>Bulking — Nutrimind</title>
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
    background:#ddd url('https://images.unsplash.com/photo-1605296867304-46d5465a13f1?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat;
    margin-bottom:16px;
  }
  .hero::after{ content:""; position:absolute; inset:0; background:linear-gradient(120deg, rgba(253,226,226,.45), rgba(251,194,235,.35)); }
  .hero-badge{ position:absolute; left:18px; bottom:16px; background:rgba(255,255,255,.9); padding:10px 14px; border-radius:14px; font-weight:700; box-shadow:0 6px 18px rgba(0,0,0,.12); }
</style>
</head>
<body>
<div class="wrap">
  <div class="hero"><div class="hero-badge">Time to bulk smart, <?= $firstName ?> 🙌</div></div>

  <div class="card">
    <h2>📈 Bulking Plan (Strength + Size)</h2>
    <span class="pill">4–6 sessions/week</span>
    <span class="pill">Heavier compounds</span>
    <span class="pill">Slight-moderate surplus</span>
    <p>Push heavier weights with lower–moderate reps. Eat in a clean surplus and track strength PRs.</p>
  </div>

  <div class="card">
    <h3>Core Compound Lifts</h3>
    <div class="grid">
      <div>
        <h4>Primary</h4>
        <ul>
          <li>Back/Front Squat — 5×5 or 4×6</li>
          <li>Deadlift (conventional/sumo) — 3–5×3–5</li>
          <li>Bench Press — 5×5 or 4×6</li>
          <li>Overhead Press — 4×5–6</li>
          <li>Weighted Pull-ups / Barbell Row — 4×5–8</li>
        </ul>
      </div>
      <div>
        <h4>Accessories</h4>
        <ul>
          <li>Hip Thrusts, Leg Press, Bulgarian Split Squat</li>
          <li>Incline DB Press, Chest Flyes</li>
          <li>Face Pulls, Rear-delt Raises</li>
          <li>Barbell/DB Curls, Skull Crushers</li>
          <li>Farmer’s Carry, Ab-wheel/Planks</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="card">
    <h3>Sample Upper/Lower Split</h3>
    <table>
      <tr><th>Day</th><th>Focus</th><th>Key Work</th></tr>
      <tr><td>Day 1</td><td>Upper (Heavy)</td><td>Bench 5×5, Row 5×5, OHP 4×5, Pull-ups weighted</td></tr>
      <tr><td>Day 2</td><td>Lower (Heavy)</td><td>Back Squat 5×5, Deadlift 3×3, Calves, Core</td></tr>
      <tr><td>Day 3</td><td>Rest/Active</td><td>Walk, mobility, light cardio 15–20 min</td></tr>
      <tr><td>Day 4</td><td>Upper (Volume)</td><td>Incline DB 4×8–10, Lat Pulldown 4×8–10, accessories</td></tr>
      <tr><td>Day 5</td><td>Lower (Volume)</td><td>Front Squat 4×6–8, RDL 4×6–8, Lunges, Leg Press</td></tr>
    </table>
  </div>

  <div class="card">
    <h3>Bulking Tips</h3>
    <ul>
      <li>Calorie surplus: ~300–500 kcal/day (track weekly weight)</li>
      <li>Protein: 1.6–2.2 g/kg; Carbs high for performance; Healthy fats for hormones</li>
      <li>Limit junk; choose calorie-dense whole foods (rice, oats, nuts, dairy, olive oil)</li>
      <li>Creatine monohydrate 3–5 g/day (if suitable for you)</li>
      <li>Deload every 6–8 weeks if fatigue builds up</li>
    </ul>
    <a class="btn" href="exercise.php">📝 Log a workout</a>
    <a class="btn" href="target.php" style="margin-left:8px">⬅ Back</a>
  </div>
</div>
</body>
</html>

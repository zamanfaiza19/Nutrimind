<?php
session_start();
require_once "dbconnect.php";
if (!isset($_SESSION["user_id"])) {
  header("Location: /My_Project/auth.php"); exit;
}
$user_name = htmlspecialchars($_SESSION["user_name"] ?? "Friend");
$first = htmlspecialchars(explode(' ', trim($user_name))[0]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Nutrimind — Home</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
  :root{
    --bg1:#fde2e2; --bg2:#f6b6b6;
    --card:#ffffff; --ink:#2d2d2d;
    --btn:#f5c2c2; --btnH:#f0a9a9;
    --pill:#fff7f7;
  }
  *{ box-sizing:border-box }
  body{
    margin:0; font-family:"Segoe UI",system-ui,Arial,sans-serif; color:var(--ink);
    min-height:100vh; display:flex; align-items:center; justify-content:center;
    background: radial-gradient(1200px 800px at -10% -10%, #ffe0f0 0%, transparent 60%),
                radial-gradient(1000px 700px at 110% 0%, #ffd6d6 0%, transparent 55%),
                linear-gradient(135deg,var(--bg1),var(--bg2));
    overflow-x:hidden;
  }
  .wrap{ width:min(980px,94vw); padding:28px 18px; }

  /* hero glass */
  .hero{
    position:relative; border-radius:26px; padding:26px 22px;
    background:linear-gradient(180deg, rgba(255,255,255,.55), rgba(255,255,255,.35));
    backdrop-filter: blur(6px);
    box-shadow:0 18px 45px rgba(0,0,0,.12);
    overflow:hidden; margin:0 auto 18px; width:100%;
  }
  .sparkle{
    position:absolute; inset:-40px -60px auto auto;
    width:260px; height:260px; border-radius:50%;
    background:radial-gradient(circle at 30% 30%, #ffffffaa 0%, #ffd6e6aa 35%, transparent 70%);
    filter:blur(10px); animation: float 8s ease-in-out infinite;
  }
  @keyframes float{ 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }

  h1{ margin:0 0 6px; font-size:2.0rem; letter-spacing:.3px }
  .sub{ margin:0; color:#6b6b6b }
  .pills{ display:flex; flex-wrap:wrap; gap:8px; margin-top:14px }
  .pill{
    background:var(--pill); border-radius:999px; padding:8px 12px; font-weight:600;
    box-shadow:0 2px 8px rgba(0,0,0,.06);
  }

  /* grid menu */
  .grid{
    display:grid; gap:12px; margin-top:18px;
    grid-template-columns:1fr;
  }
  @media (min-width: 720px){
    .grid{ grid-template-columns:1fr 1fr }
  }

  .card{
    background:var(--card); border-radius:20px; padding:20px;
    box-shadow:0 14px 34px rgba(0,0,0,.10);
  }

  .btn{
    display:flex; align-items:center; gap:12px; padding:14px 16px;
    background:var(--btn); color:#2d1f1f; border:none; border-radius:14px;
    text-decoration:none; font-weight:700; box-shadow:0 2px 6px rgba(0,0,0,.08);
    transition:transform .18s ease, box-shadow .18s ease, background .18s ease;
  }
  .btn:hover{ background:var(--btnH); transform:translateY(-2px); box-shadow:0 8px 16px rgba(0,0,0,.18) }
  .ico{ font-size:1.25rem; width:28px; text-align:center }

  /* tip widget */
  .tip{
    margin-top:10px; background:#fff; border-radius:14px; padding:12px 14px;
    box-shadow:0 8px 20px rgba(0,0,0,.08); display:flex; gap:10px; align-items:flex-start;
  }
  .tip strong{ display:block; margin-bottom:4px }
  .foot{
    text-align:center; margin-top:16px;
  }
  .logout{
    display:inline-block; margin-top:8px; color:#c0392b; font-weight:700; text-decoration:none;
  }
  .logout:hover{ text-decoration:underline }
</style>
</head>
<body>
  <div class="wrap">
    <!-- HERO -->
    <div class="hero">
      <div class="sparkle" aria-hidden="true"></div>
      <h1>Welcome, <?= $first ?> <span aria-hidden="true">👋</span></h1>
      <p class="sub">Pick your next step towards a healthier you.</p>
      <div class="pills">
        <span class="pill">🌸 Nutrimind</span>
        <span class="pill">✨ Balanced Life</span>
        <span class="pill">📅 <?= date('D, M j') ?></span>
      </div>

      <!-- Daily Tip (static sample—swap with DB later if you like) -->
      <div class="tip" style="margin-top:14px">
        <div class="ico">🌱</div>
        <div>
          <strong>Daily Nutrition Tip</strong>
          Start your day with a glass of water and include protein in breakfast to stay full longer.
        </div>
      </div>
    </div>

    <!-- MENU GRID -->
    <div class="grid">
      <div class="card">
        <a href="bmi.php" class="btn"><span class="ico">📏</span><span>Calculate BMI</span></a>
        <a href="intake.php" class="btn"><span class="ico">🍽️</span><span>Track Daily Intake</span></a>
        <a href="sleep.php" class="btn"><span class="ico">😴</span><span>Sleep Tracker</span></a>
        <a href="exercise.php" class="btn"><span class="ico">🏋️</span><span>Exercise Log</span></a>
      </div>

      <div class="card">
        <a href="music.php" class="btn"><span class="ico">🎵</span><span>Music</span></a>
        <a href="allergy.php" class="btn"><span class="ico">🚫</span><span>Food Allergen</span></a>
        <a href="updatebmi.php" class="btn"><span class="ico">⚖️</span><span>Update Current Weight</span></a>
        <a href="target.php" class="btn"><span class="ico">🎯</span><span>Set Fitness Goal</span></a>
      </div>
    </div>

    <div class="foot">
      <a href="logout.php" class="logout">🚪 Logout</a>
    </div>
  </div>
</body>
</html>

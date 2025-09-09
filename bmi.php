<?php
session_start();
require_once __DIR__ . '/dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /My_Project/auth.php");
    exit;
}

$memberId = (int)$_SESSION['user_id'];

$firstName = 'Friend';
$h_cm = 0.0;
$w_kg = 0.0;

$stmt = $conn->prepare("SELECT First_name, Height_cm, Weight_kg FROM member WHERE Member_id = ? LIMIT 1");
$stmt->bind_param("i", $memberId);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $firstName = $row['First_name'] ?? 'Friend';
    $h_cm = isset($row['Height_cm']) ? (float)$row['Height_cm'] : 0.0;
    $w_kg = isset($row['Weight_kg']) ? (float)$row['Weight_kg'] : 0.0;
}
$stmt->close();

$bmi = null;
$category = 'Unknown';
if ($h_cm > 0 && $w_kg > 0) {
    $h_m = $h_cm / 100.0;
    $bmi = $w_kg / ($h_m * $h_m);
    if ($bmi < 18.5) {
        $category = 'Underweight';
    } elseif ($bmi < 25.0) {
        $category = 'Normal';
    } elseif ($bmi < 30.0) {
        $category = 'Overweight';
    } else {
        $category = 'Obese';
    }
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>BMI • Nutrimind</title>


<style>
  :root{
    --bg: #ffe6ee;        
    --card: #ffffff;      
    --text: #222222;      
    --muted: #666666;     
    --accent: #ff6fa3;    
    --accent-2: #ffa6c6;  
    --ring: rgba(255, 111, 163, 0.25);
  }
  * { box-sizing: border-box; }
  html, body { height: 100%; }
  body{
    margin: 0;
    background: var(--bg) !important;
    color: var(--text) !important;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Inter, Arial, "Noto Sans", "Helvetica Neue", sans-serif;
    -webkit-font-smoothing: antialiased;
    line-height: 1.45;
  }
  .wrap{
    max-width: 960px;
    margin: 0 auto;
    padding: 24px;
  }
  .header{
    display:flex; align-items:center; justify-content:space-between; gap:16px;
    margin-bottom: 16px;
  }
  .brand{
    display:flex; align-items:center; gap:12px;
    font-weight: 700; font-size: 20px;
    color: var(--text);
    text-decoration: none;
  }
  .brand .dot{
    width: 12px; height: 12px; border-radius: 50%;
    background: var(--accent);
    box-shadow: 0 0 0 6px var(--ring);
  }
  .content{
    display:grid;
    grid-template-columns: 1fr;
    gap: 16px;
  }
  @media (min-width: 900px){
    .content{ grid-template-columns: 1.3fr .7fr; }
  }
  .card{
    background: var(--card) !important;
    color: var(--text) !important;
    border: 1px solid #f2d7e2;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.06);
    padding: 20px;
  }
  .card h2{
    margin: 0 0 12px 0;
    font-size: 18px;
  }
  .muted{ color: var(--muted); }
  .metric{
    display:flex; align-items:baseline; gap:10px; margin: 8px 0;
  }
  .metric .value{
    font-size: 32px; font-weight: 800;
  }
  .pill{
    display:inline-block; padding:6px 10px; border-radius:999px;
    background: var(--accent); color:#fff; font-weight:600; font-size:12px; letter-spacing: .25px;
  }
  .list{
    margin: 0; padding: 0; list-style: none;
  }
  .list li{
    padding: 8px 0; border-bottom: 1px dashed #f3cddd;
  }
  .list li:last-child{ border-bottom: none; }
  .btn{
    display:inline-block; padding:10px 14px; border-radius: 999px;
    border: 1px solid var(--accent); background: var(--accent); color:#fff; text-decoration:none; font-weight: 700;
  }
  .btn.outline{
    background: transparent !important; color: var(--accent) !important;
  }
  a{ color: var(--accent) !important; text-decoration: none; }
  a:hover{ text-decoration: underline; }
 
  .underweight, .normal, .overweight, .obese{
    background: inherit !important; color: inherit !important;
  }
  .grid2{
    display:grid; grid-template-columns: 1fr 1fr; gap:8px;
  }
  .field{
    display:flex; flex-direction:column; gap:6px;
  }
  input[type="number"], input[type="text"]{
    padding: 10px 12px; border-radius: 12px; border: 1px solid #f2d7e2; outline: none;
  }
  input:focus{ border-color: var(--accent); box-shadow: 0 0 0 4px var(--ring); }
</style>
</head>
<body>
  <div class="wrap">
    <div class="header">
      <a class="brand" href="home.php">
        <span class="dot"></span>
        <span>Nutrimind</span>
      </a>
      <div class="muted">Welcome, <strong><?php echo h($firstName); ?></strong></div>
    </div>

    <div class="content">
     
      <div class="card">
        <h2>Your BMI</h2>
        <p class="muted">Based on your profile details.</p>

        <div class="metric">
          <div class="value">
            <?php
              echo $bmi !== null ? number_format($bmi, 1) : '--';
            ?>
          </div>
          <div class="pill"><?php echo h($category); ?></div>
        </div>

        <ul class="list">
          <li><strong>Height:</strong> <?php echo $h_cm > 0 ? h($h_cm.' cm') : 'Not set'; ?></li>
          <li><strong>Weight:</strong> <?php echo $w_kg > 0 ? h($w_kg.' kg') : 'Not set'; ?></li>
        </ul>

        <p class="muted" style="margin-top:12px;">
          <?php if ($bmi === null): ?>
            Add your height and weight in your profile to see your BMI.
          <?php else: ?>
            BMI categories: &lt;18.5 Underweight, 18.5–24.9 Normal, 25–29.9 Overweight, ≥30 Obese.
          <?php endif; ?>
        </p>
      </div>

      <div class="card">
        <h2>Quick Links</h2>
        <ul class="list">
          <li><a href="profile.php">Update height &amp; weight</a></li>
          <li><a href="meal.php">Log today’s meals</a></li>
          <li><a href="sleep.php">Log sleep</a></li>
          <li><a href="music.php">Music by mood</a></li>
        </ul>
        <div style="margin-top:12px;">
          <a class="btn outline" href="home.php">⬅ Back to Home</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

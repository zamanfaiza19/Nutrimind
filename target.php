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
<title>Choose Target — Nutrimind</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
  :root{ --bg1:#fde2e2; --bg2:#fbc2eb; --card:#fff; --btn:#e9c3c3; --btnH:#e8b3b3; }
  body{ margin:0; font-family:"Segoe UI",Arial,sans-serif; background:linear-gradient(135deg,var(--bg1),var(--bg2)); min-height:100vh; display:flex; align-items:center; justify-content:center; }
  .card{ background:var(--card); border-radius:20px; padding:30px; box-shadow:0 18px 45px rgba(0,0,0,.18); text-align:center; }
  h2{ margin-bottom:20px; }
  .btn{ display:block; background:var(--btn); padding:14px; border-radius:12px; text-decoration:none; color:#222; font-weight:700; margin:12px 0; }
  .btn:hover{ background:var(--btnH); }
</style>
</head>
<body>
  <div class="card">
    <h2>🎯 Choose Your Goal, <?= $firstName ?> </h2>
    <a class="btn" href="weightloss.php">Weight Loss</a>
    <a class="btn" href="musclegain.php">Muscle Gain</a>
    <a class="btn" href="bulking.php">Bulking</a>
    <br>
    <a class="btn" href="home.php">⬅ Back to Home</a>
  </div>
</body>
</html>